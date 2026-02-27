<?php
/**
 * ROBUST I18N SYNC & TRANSLATE
 * Source of truth: Front/public/i18n/es.json
 */

require 'Back/vendor/autoload.php';
$app = require_once 'Back/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$i18nDir = 'Front/public/i18n';
$sourceFile = "$i18nDir/es.json";
$esContent = json_decode(file_get_contents($sourceFile), true);

function flattenArray($array, $prefix = '')
{
    $result = [];
    foreach ($array as $key => $value) {
        $fullKey = $prefix ? "$prefix.$key" : $key;
        if (is_array($value) && !array_is_list($value)) {
            $result = array_merge($result, flattenArray($value, $fullKey));
        } else {
            $result[$fullKey] = $value;
        }
    }
    return $result;
}

function unflattenArray($flatArray)
{
    $result = [];
    foreach ($flatArray as $key => $value) {
        $parts = explode('.', $key);
        $temp = &$result;
        foreach ($parts as $part) {
            if (!isset($temp[$part]))
                $temp[$part] = [];
            $temp = &$temp[$part];
        }
        $temp = $value;
    }
    return $result;
}

$esFlat = flattenArray($esContent);
$languages = ['en', 'ca', 'fr', 'de', 'it', 'pt', 'ru', 'zh', 'hi', 'ar', 'tr', 'uk', 'nl', 'val'];

echo "Starting Sync & Translation Process...\n";

foreach ($languages as $lang) {
    echo "Processing [$lang]...\n";
    $filePath = "$i18nDir/$lang.json";

    $currentFlat = [];
    if (file_exists($filePath)) {
        $data = json_decode(file_get_contents($filePath), true);
        if ($data)
            $currentFlat = flattenArray($data);
    }

    $newFlat = [];
    $toTranslate = [];

    // 1. Determine what to keep and what to translate
    foreach ($esFlat as $key => $esValue) {
        $currentValue = $currentFlat[$key] ?? null;

        // Keep translation if:
        // - Exists
        // - Is NOT equal to Spanish (if it's not English/Catalan where we might want exact matches sometimes, but usually translate everything)
        // - Is NOT empty
        if ($currentValue !== null && $currentValue !== $esValue && $currentValue !== "") {
            $newFlat[$key] = $currentValue;
        } else {
            // Need translation
            $toTranslate[$key] = $esValue;
        }
    }

    echo "  - " . count($newFlat) . " keys kept.\n";
    echo "  - " . count($toTranslate) . " keys to translate.\n";

    if (!empty($toTranslate)) {
        $googleLang = ($lang === 'val') ? 'ca' : $lang;

        // Batch translation (TranslationService style)
        $chunks = array_chunk($toTranslate, 15, true);
        foreach ($chunks as $chunk) {
            $texts = array_values($chunk);
            $keys = array_keys($chunk);
            $combined = implode(' [[[|]]] ', $texts);

            try {
                $response = Http::get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl' => 'es',
                    'tl' => $googleLang,
                    'dt' => 't',
                    'q' => $combined,
                ]);

                if ($response->successful()) {
                    $body = $response->json();
                    $translatedBulk = collect($body[0])->pluck(0)->filter(fn($i) => is_string($i))->implode('');
                    $translatedParts = explode('[[[|]]]', $translatedBulk);

                    foreach ($keys as $index => $key) {
                        $newFlat[$key] = trim($translatedParts[$index] ?? $chunk[$key]);
                    }
                    echo ".";
                } else {
                    echo "X";
                    // Failover: just use Spanish for now to avoid breaking keys
                    foreach ($keys as $key)
                        $newFlat[$key] = $esFlat[$key];
                }
            } catch (\Exception $e) {
                echo "E";
                foreach ($keys as $key)
                    $newFlat[$key] = $esFlat[$key];
            }
            usleep(200000); // 0.2s
        }
    }

    // Sort keys according to es.json order
    $finalFlat = [];
    foreach ($esFlat as $key => $v) {
        $finalFlat[$key] = $newFlat[$key] ?? $v;
    }

    $finalContent = unflattenArray($finalFlat);
    file_put_contents($filePath, json_encode($finalContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "\n  - Done. Total keys: " . count($finalFlat) . "\n";
}

echo "ALL LANGUAGES SYNCED WITH es.json STRUCTURE.\n";
