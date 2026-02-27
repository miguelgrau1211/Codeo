const fs = require('fs');
const path = require('path');
const https = require('https');

const i18nDir = path.join(__dirname, 'Front', 'public', 'i18n');
const sourceFile = path.join(i18nDir, 'es.json');
const sourceData = JSON.parse(fs.readFileSync(sourceFile, 'utf8'));

// Prioritize languages from user feedback
const languages = ['ru', 'uk', 'zh', 'hi', 'ar', 'it', 'pt', 'tr', 'nl', 'fr', 'de', 'en', 'ca', 'val'];

function getAllKeys(obj, prefix = '') {
    let keys = {};
    for (const key in obj) {
        const fullKey = prefix ? `${prefix}.${key}` : key;
        if (typeof obj[key] === 'object' && obj[key] !== null && !Array.isArray(obj[key])) {
            Object.assign(keys, getAllKeys(obj[key], fullKey));
        } else {
            keys[fullKey] = obj[key];
        }
    }
    return keys;
}

const sourceKeysMap = getAllKeys(sourceData);

async function translate(texts, targetLang) {
    const googleLang = targetLang === 'val' ? 'ca' : targetLang;
    const delimiter = " [[[|]]] ";
    const combined = texts.join(delimiter);
    const url = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=es&tl=${googleLang}&dt=t&q=${encodeURIComponent(combined)}`;

    return new Promise((resolve, reject) => {
        https.get(url, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                try {
                    const parsed = JSON.parse(data);
                    // Google combines the response but we need to extract the parts
                    // The first element is an array of [translated, source, ...]
                    let translatedFull = parsed[0].map(x => x[0]).join('');
                    // Split the full string back into parts using the delimiter
                    let parts = translatedFull.split(/[\s]?\[\[\[\|\]\]\][\s]?/).map(s => s.trim());
                    resolve(parts);
                } catch (e) {
                    console.error("Parse Error:", data);
                    reject(e);
                }
            });
        }).on('error', reject);
    });
}

const delay = ms => new Promise(res => setTimeout(res, ms));

async function processLanguage(lang) {
    console.log(`\nStarting [${lang}]...`);
    const filePath = path.join(i18nDir, `${lang}.json`);

    let targetData = {};
    if (fs.existsSync(filePath)) {
        try {
            targetData = JSON.parse(fs.readFileSync(filePath, 'utf8'));
        } catch (e) {
            targetData = {};
        }
    }
    const targetKeysMap = getAllKeys(targetData);

    const keysToTranslate = [];
    for (const key in sourceKeysMap) {
        const val = targetKeysMap[key];
        // Translate if missing OR if it's identical to Spanish (placeholders)
        if (!targetKeysMap.hasOwnProperty(key) || (val === sourceKeysMap[key] && lang !== 'es')) {
            keysToTranslate.push(key);
        }
    }

    console.log(`  - Translating ${keysToTranslate.length} keys...`);

    if (keysToTranslate.length > 0) {
        const batchSize = 25; // Increased batch size
        for (let i = 0; i < keysToTranslate.length; i += batchSize) {
            const batch = keysToTranslate.slice(i, i + batchSize);
            const sourceTexts = batch.map(k => sourceKeysMap[k]);

            let success = false;
            let retries = 5;
            while (!success && retries > 0) {
                try {
                    const translated = await translate(sourceTexts, lang);
                    batch.forEach((key, idx) => {
                        targetKeysMap[key] = translated[idx] || sourceKeysMap[key];
                    });
                    success = true;
                    process.stdout.write('.');
                } catch (e) {
                    retries--;
                    process.stdout.write('!');
                    await delay(1500);
                }
            }
            await delay(400);
        }
    }

    function reconstruct(sourceObj, currentPrefix = '') {
        const newObj = {};
        for (const key in sourceObj) {
            const fullKey = currentPrefix ? `${currentPrefix}.${key}` : key;
            if (typeof sourceObj[key] === 'object' && sourceObj[key] !== null && !Array.isArray(sourceObj[key])) {
                newObj[key] = reconstruct(sourceObj[key], fullKey);
            } else {
                newObj[key] = targetKeysMap[fullKey] || sourceObj[key];
            }
        }
        return newObj;
    }

    const finalData = reconstruct(sourceData);
    fs.writeFileSync(filePath, JSON.stringify(finalData, null, 4), 'utf8');
    console.log(`\n  - [${lang}] Saved successfully.`);
}

async function run() {
    for (const lang of languages) {
        await processLanguage(lang);
    }
    console.log("\nMISSION ACCOMPLISHED: ALL LANGUAGES FULLY SYNCED AND TRANSLATED.");
}

run();
