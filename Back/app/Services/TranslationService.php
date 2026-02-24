<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Languages we actively translate via Google.
     * Must match the codes in the frontend LanguageService.
     * Spanish ('es') is excluded because it is the source language.
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

    /** Delimiter for bulk translation logic. High uniqueness to avoid collisions with user text. */
    private const BULK_DELIMITER = ' [[[|]]] ';

    /**
     * Fields that contain translatable free-text stored in Spanish in the DB.
     */
    private const TRANSLATABLE_FIELDS_LEVEL = ['titulo', 'descripcion', 'contenido_teorico'];
    private const TRANSLATABLE_FIELDS_LOGRO = ['nombre', 'descripcion'];

    /**
     * Circuit-breaker flag: once Google Translate fails within a request,
     * skip all subsequent calls to avoid cascading timeouts.
     */
    private bool $translationAvailable = true;

    /**
     * Translate a level/challenge.
     */
    public function translateNivel(array|object $nivel, string $locale): array
    {
        return $this->translateFields($nivel, $locale, self::TRANSLATABLE_FIELDS_LEVEL);
    }

    /**
     * Translate an achievement (logro).
     */
    public function translateLogro(array|object $logro, string $locale): array
    {
        return $this->translateFields($logro, $locale, self::TRANSLATABLE_FIELDS_LOGRO);
    }

    /**
     * Bulk translate a collection of records.
     * Use this for Lists (Achievements, Levels) to avoid N+1 HTTP calls to Google.
     */
    public function translateCollection(iterable $records, string $locale, string $type = 'logro'): array
    {
        $lang = $this->getValidTargetLang($locale);
        $fields = ($type === 'logro') ? self::TRANSLATABLE_FIELDS_LOGRO : self::TRANSLATABLE_FIELDS_LEVEL;

        if ($lang === 'es' || !$this->translationAvailable) {
            return $this->normalizeItems($records);
        }

        $items = $this->normalizeItems($records);
        $toTranslate = [];

        // 1. Identify what needs translation (not in cache)
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

        // 2. Perform Bulk Translation
        // We join texts using a delimiter. We might need multiple chunks if text is too long.
        $chunks = $this->chunkTranslationData($toTranslate);

        foreach ($chunks as $chunk) {
            $combinedText = collect($chunk)->pluck('text')->implode(self::BULK_DELIMITER);
            $translatedBulk = $this->callGoogleTranslate($combinedText, $lang);

            if ($translatedBulk === null) {
                $this->translationAvailable = false;
                break; // Stop translating collection if API fails
            }

            $translatedParts = explode(trim(self::BULK_DELIMITER), $translatedBulk);

            // Map back and cache
            foreach ($chunk as $i => $meta) {
                $val = trim($translatedParts[$i] ?? $meta['text']);
                $items[$meta['item_index']][$meta['field']] = $val;

                // Cache it for future requests
                $cacheKey = "trans_{$lang}_" . md5($meta['text']);
                Cache::put($cacheKey, $val, now()->addDays(7));
            }
        }

        return $items;
    }

    /** Helper for backwards compatibility / single items */
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
        // Safety: ensure characters like | are preserved by not over-cleaning
        try {
            // Google Translate handles many languages. 'val' is not standard, use 'ca'.
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

    /** Split bulk translation into smaller chunks to avoid URL length limits (approx 2000 chars) */
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

        // Log to help debugging why 'it' might persist
        // Log::debug("TranslationService: Resolved locale '{$lang}' from header '{$header}'");

        return $lang ?: 'es';
    }
}
