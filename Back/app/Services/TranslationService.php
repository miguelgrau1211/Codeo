<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de Traducción Dinámica para Codeo.
 * 
 * Este servicio se encarga de traducir contenido almacenado en la base de datos (originalmente en castellano)
 * al idioma preferido del usuario mediante la API de Google Translate.
 * 
 * Implementa estrategias de optimización como:
 * - Caché de resultados para evitar llamadas repetitivas.
 * - Traducción por lotes (Bulk) para evitar el problema N+1 en colecciones.
 * - Circuit-breaker para evitar retrasos si la API externa falla.
 */
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
    private const TRANSLATABLE_FIELDS_LEVEL = ['titulo', 'descripcion', 'contenido_teorico', 'codigo_inicial'];
    private const TRANSLATABLE_FIELDS_LOGRO = ['nombre', 'descripcion'];
    private const TRANSLATABLE_FIELDS_TEMA = ['nombre', 'descripcion'];

    /**
     * Bandera de "Circuit-breaker": si Google Translate falla en una solicitud, 
     * se omiten las siguientes para evitar retrasos en cascada (timeouts).
     */
    private bool $translationAvailable = true;

    /**
     * Traduce los campos de un objeto Nivel al idioma solicitado.
     * 
     * @param array|object $nivel El registro del nivel o desafío.
     * @param string $locale Código de idioma (ej: 'en', 'fr').
     * @return array Datos del nivel con los campos traducidos.
     */
    public function translateNivel(array|object $nivel, string $locale): array
    {
        return $this->translateFields($nivel, $locale, self::TRANSLATABLE_FIELDS_LEVEL);
    }

    /**
     * Traduce los campos de un objeto Logro al idioma solicitado.
     * 
     * @param array|object $logro El registro del logro.
     * @param string $locale Código de idioma.
     * @return array Datos del logro traducidos.
     */
    public function translateLogro(array|object $logro, string $locale): array
    {
        return $this->translateFields($logro, $locale, self::TRANSLATABLE_FIELDS_LOGRO);
    }

    /**
     * Traduce los campos de un objeto Tema Visual al idioma solicitado.
     * 
     * @param array|object $tema El registro del tema.
     * @param string $locale Código de idioma.
     * @return array Datos del tema traducidos.
     */
    public function translateTema(array|object $tema, string $locale): array
    {
        return $this->translateFields($tema, $locale, self::TRANSLATABLE_FIELDS_TEMA);
    }

    /**
     * Traduce una colección de registros (Logros, Niveles, Temas) de forma masiva.
     * 
     * Optimiza el rendimiento concatenando todos los textos en una única petición a Google.
     * Verifica la existencia en caché antes de enviar nuevos textos a traducir.
     * 
     * @param iterable $records Colección de modelos o arrays.
     * @param string $locale Idioma de destino.
     * @param string $type Tipo de entidad ('logro', 'nivel', 'tema').
     * @return array Colección procesada con las traducciones aplicadas.
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

    /**
     * Helper específico para colecciones de logros (mantiene compatibilidad con código anterior).
     */
    public function translateLogrosCollection(iterable $logros, string $locale): array
    {
        return $this->translateCollection($logros, $locale, 'logro');
    }

    /**
     * Procesa la traducción campo por campo de un registro individual.
     * 
     * @param array|object $record Registro a traducir.
     * @param string $locale Idioma destino.
     * @param array $fields Lista de campos que deben ser traducidos.
     * @return array Array con los datos (posiblemente) traducidos.
     */
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

    /**
     * Obtiene una traducción desde la caché o la solicita a la API si no existe.
     * 
     * @param string $text Texto original en castellano.
     * @param string $targetLang Idioma destino (ISO).
     * @return string|null El texto traducido, o null si la API falla.
     */
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

    /**
     * Realiza la llamada HTTP a la API gratuita de Google Translate.
     * 
     * @param string $text Texto (o cadena de textos unidos por delimitador) a traducir.
     * @param string $targetLang Código de idioma compatible con Google.
     * @return string|null Respuesta de Google o null en caso de error.
     */
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

    /**
     * Verifica si el locale solicitado está soportado y devuelve el código ISO base.
     * Si no está en la lista blanca, devuelve 'es' (sin traducción).
     * 
     * @param string $locale Locale completo (ej: 'en-US' o 'en').
     * @return string Código ISO de dos letras soportado.
     */
    private function getValidTargetLang(string $locale): string
    {
        $lang = strtolower(explode('-', $locale)[0]);
        return in_array($lang, self::SUPPORTED_LOCALES) ? $lang : 'es';
    }

    /**
     * Convierte una colección de objetos/modelos en una lista de arrays planos.
     * 
     * @param iterable $records Colección de elementos.
     * @return array Lista de arrays.
     */
    private function normalizeItems(iterable $records): array
    {
        $result = [];
        foreach ($records as $record) {
            $result[] = is_array($record) ? $record : (method_exists($record, 'toArray') ? $record->toArray() : (array) $record);
        }
        return $result;
    }

    /** 
     * Divide los datos a traducir en fragmentos más pequeños.
     * 
     * El límite de 1500 caracteres asegura que la URL de la petición GET (después del encoding)
     * no exceda el límite estándar de los servidores y proxies (2000-4000 caracteres).
     * 
     * @param array $data Lista de metadatos de traducción.
     * @return array Chunks de datos listos para procesar.
     */
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

    /**
     * Resuelve el idioma del usuario basándose en el header HTTP 'Accept-Language'.
     * 
     * @param \Illuminate\Http\Request $request
     * @return string Idioma base (ej: 'es', 'en', 'fr').
     */
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
