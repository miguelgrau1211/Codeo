<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Idiomas que traducimos activamente mediante la API de Google.
     * Debe coincidir con los códigos definidos en el frontend (LanguageService).
     * El castellano ('es') se excluye porque es el idioma de origen en la BD.
     */
    private const SUPPORTED_LOCALES = [
        'en',
        'fr',
        'de',
        'pt',
        'ca',
        'it',
        'nl',
        'ru',
        'zh',
        'tr',
        'uk',
        'ar',
        'hi',
        'val',
    ];

    /** Delimitador para la lógica de traducción masiva (Bulk). Alta unicidad para evitar colisiones. */
    private const BULK_DELIMITER = ' [[[|]]] ';

    /**
     * Campos que contienen texto libre traducible almacenado en castellano en la base de datos.
     */
    private const TRANSLATABLE_FIELDS_LEVEL = ['titulo', 'descripcion', 'contenido_teorico'];
    private const TRANSLATABLE_FIELDS_LOGRO = ['nombre', 'descripcion'];
    private const TRANSLATABLE_FIELDS_TEMA = ['nombre', 'descripcion'];

    /**
     * Bandera de "Circuit-breaker": si Google Translate falla en una solicitud, 
     * se omiten las siguientes para evitar retrasos en cascada (timeouts).
     */
    private bool $translationAvailable = true;

    /**
     * Traduce un nivel o desafío.
     */
    public function translateNivel(array|object $nivel, string $locale): array
    {
        return $this->translateFields($nivel, $locale, self::TRANSLATABLE_FIELDS_LEVEL);
    }

    /**
     * Traduce un logro.
     */
    public function translateLogro(array|object $logro, string $locale): array
    {
        return $this->translateFields($logro, $locale, self::TRANSLATABLE_FIELDS_LOGRO);
    }

    /**
     * Traduce un tema visual.
     */
    public function translateTema(array|object $tema, string $locale): array
    {
        return $this->translateFields($tema, $locale, self::TRANSLATABLE_FIELDS_TEMA);
    }

    /**
     * Traduce una colección de registros de forma masiva (Bulk).
     * Se usa para listas (Logros, Niveles) para evitar llamadas N+1 a Google Translate.
     */
    public function translateCollection(iterable $records, string $locale, string $type = 'logro'): array
    {
        $lang = $this->getValidTargetLang($locale);

        $fields = match ($type) {
            'logro' => self::TRANSLATABLE_FIELDS_LOGRO,
            'nivel' => self::TRANSLATABLE_FIELDS_LEVEL,
            'tema' => self::TRANSLATABLE_FIELDS_TEMA,
            default => self::TRANSLATABLE_FIELDS_LOGRO
        };

        if ($lang === 'es' || !$this->translationAvailable) {
            return $this->normalizeItems($records);
        }

        $items = $this->normalizeItems($records);
        $toTranslate = [];

        // 1. Identificar qué necesita traducción (lo que no esté en caché)
        foreach ($items as $index => $item) {
            foreach ($fields as $field) {
                if (!empty($item[$field]) && is_string($item[$field])) {
                    $cacheKey = "trans_{$lang}_" . md5($item[$field]);
                    if (Cache::has($cacheKey)) {
                        $items[$index][$field] = Cache::get($cacheKey);
                    } else {
                        $toTranslate[] = [
                            'item_index' => $index,
                            'field' => $field,
                            'text' => $item[$field]
                        ];
                    }
                }
            }
        }

        if (empty($toTranslate)) {
            return $items;
        }

        // 2. Realizar Traducción en Lote (Bulk)
        // Unimos los textos usando un delimitador. Se usan fragmentos (chunks) si el texto total es muy largo.
        $chunks = $this->chunkTranslationData($toTranslate);

        foreach ($chunks as $chunk) {
            // Unimos todos los textos del fragmento con el delimitador especial
            $combinedText = collect($chunk)->pluck('text')->implode(self::BULK_DELIMITER);
            $translatedBulk = $this->callGoogleTranslate($combinedText, $lang);

            if ($translatedBulk === null) {
                // Si la API falla (ej: timeout), activamos el "circuit-breaker" para el resto de la petición
                $this->translationAvailable = false;
                break;
            }

            // Dividimos el texto traducido de vuelta en sus partes originales usando el delimitador.
            // La expresión regular contempla posibles espacios añadidos por el traductor alrededor de los corchetes.
            $translatedParts = preg_split('/\s?\[\[\[\|\]\]\]\s?/', $translatedBulk);

            // Mapeamos cada traducción a su posición original en la colección
            foreach ($chunk as $i => $meta) {
                // Si por algún error la división devuelve menos partes, mantenemos el texto original (meta['text'])
                $val = trim($translatedParts[$i] ?? $meta['text']);
                $items[$meta['item_index']][$meta['field']] = $val;

                // Guardamos en caché por 7 días. La clave es el MD5 del texto en castellano original.
                $cacheKey = "trans_{$lang}_" . md5($meta['text']);
                Cache::put($cacheKey, $val, now()->addDays(7));
            }
        }

        return $items;
    }

    /** Helper para compatibilidad con colecciones de logros */
    public function translateLogrosCollection(iterable $logros, string $locale): array
    {
        return $this->translateCollection($logros, $locale, 'logro');
    }

    private function translateFields(array|object $record, string $locale, array $fields): array
    {
        $data = is_array($record) ? $record : (method_exists($record, 'toArray') ? $record->toArray() : (array) $record);
        $lang = $this->getValidTargetLang($locale);

        if ($lang === 'es' || !$this->translationAvailable) {
            return $data;
        }

        foreach ($fields as $field) {
            if (!empty($data[$field]) && is_string($data[$field])) {
                $translated = $this->translateCached($data[$field], $lang);
                if ($translated === null) {
                    $this->translationAvailable = false;
                    return $data;
                }
                $data[$field] = $translated;
            }
        }
        return $data;
    }

    private function translateCached(string $text, string $targetLang): ?string
    {
        $cacheKey = "trans_{$targetLang}_" . md5($text);
        if (Cache::has($cacheKey))
            return Cache::get($cacheKey);

        $result = $this->callGoogleTranslate($text, $targetLang);
        if ($result === null)
            return null;

        Cache::put($cacheKey, $result, now()->addDays(7));
        return $result;
    }

    private function callGoogleTranslate(string $text, string $targetLang): ?string
    {
        // Seguridad: asegurar que caracteres como | se preserven no limpiando en exceso
        try {
            // Google Translate soporta muchos idiomas. 'val' no es estándar, usamos 'ca' para la API.
            $googleLang = ($targetLang === 'val') ? 'ca' : $targetLang;

            $response = Http::timeout(5)->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => 'es',
                'tl' => $googleLang,
                'dt' => 't',
                'q' => $text,
            ]);

            if ($response->successful()) {
                $body = $response->json();
                if (!is_array($body) || !isset($body[0]))
                    return $text;

                return collect($body[0])->pluck(0)->filter(fn($i) => is_string($i))->implode('');
            }

            Log::warning("TranslationService: API Error {$response->status()} for '{$targetLang}'");
            return null;
        } catch (\Throwable $e) {
            Log::warning("TranslationService: Request failed for '{$targetLang}': " . $e->getMessage());
            return null;
        }
    }

    private function getValidTargetLang(string $locale): string
    {
        $lang = strtolower(explode('-', $locale)[0]);
        return in_array($lang, self::SUPPORTED_LOCALES) ? $lang : 'es';
    }

    private function normalizeItems(iterable $records): array
    {
        $result = [];
        foreach ($records as $record) {
            $result[] = is_array($record) ? $record : (method_exists($record, 'toArray') ? $record->toArray() : (array) $record);
        }
        return $result;
    }

    /** Divide la traducción masiva en fragmentos más pequeños para evitar límites de longitud en la URL (aprox 2000 chars) */
    private function chunkTranslationData(array $data): array
    {
        $chunks = [];
        $currentChunk = [];
        $currentLength = 0;

        foreach ($data as $item) {
            $len = strlen($item['text']);
            if ($currentLength + $len > 1500 && !empty($currentChunk)) {
                $chunks[] = $currentChunk;
                $currentChunk = [];
                $currentLength = 0;
            }
            $currentChunk[] = $item;
            $currentLength += $len;
        }
        if (!empty($currentChunk))
            $chunks[] = $currentChunk;

        return $chunks;
    }

    public static function resolveLocale(\Illuminate\Http\Request $request): string
    {
        $header = $request->header('Accept-Language', 'es');
        $primary = explode(',', $header)[0];
        $lang = strtolower(explode('-', trim($primary))[0]);

        // Registro para depurar por qué 'it' (u otros) podría persistir en el header
        // Log::debug("TranslationService: Resolved locale '{$lang}' from header '{$header}'");

        return $lang ?: 'es';
    }
}
