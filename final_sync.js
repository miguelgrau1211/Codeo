const fs = require('fs');
const path = require('path');
const https = require('https');

const i18nDir = path.join(__dirname, 'Front', 'public', 'i18n');
const sourceFile = path.join(i18nDir, 'es.json');
const sourceData = JSON.parse(fs.readFileSync(sourceFile, 'utf8'));

const languages = ['en', 'ca', 'fr', 'de', 'it', 'pt', 'ru', 'zh', 'hi', 'ar', 'tr', 'uk', 'nl', 'val'];

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

async function translate(text, targetLang) {
    const googleLang = targetLang === 'val' ? 'ca' : targetLang;
    const url = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=es&tl=${googleLang}&dt=t&q=${encodeURIComponent(text)}`;

    return new Promise((resolve, reject) => {
        https.get(url, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                try {
                    const parsed = JSON.parse(data);
                    // Single text translation returns [[["translated", "source", ...]]]
                    // Google might split long text into multiple parts
                    let fullTranslated = parsed[0].map(x => x[0]).join('');
                    resolve(fullTranslated);
                } catch (e) {
                    reject(e);
                }
            });
        }).on('error', reject);
    });
}

async function translateBatch(texts, targetLang) {
    const delimiter = " [[[|]]] ";
    const combined = texts.join(delimiter);
    const result = await translate(combined, targetLang);
    return result.split(/[\s]?\[\[\[\|\]\]\][\s]?/).map(s => s.trim());
}

const delay = ms => new Promise(res => setTimeout(res, ms));

async function processLanguage(lang) {
    console.log(`Processing [${lang}]...`);
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
        // If missing or if it's the same as Spanish (placeholder)
        if (!targetKeysMap.hasOwnProperty(key) || (val === sourceKeysMap[key] && lang !== 'es')) {
            keysToTranslate.push(key);
        }
    }

    console.log(`  - Found ${keysToTranslate.length} keys needing translation.`);

    if (keysToTranslate.length > 0) {
        // Build the new object structure from sourceData
        // (This ensures we have exactly the same keys and order)

        const batchSize = 10;
        for (let i = 0; i < keysToTranslate.length; i += batchSize) {
            const batch = keysToTranslate.slice(i, i + batchSize);
            const sourceTexts = batch.map(k => sourceKeysMap[k]);

            let success = false;
            let retries = 3;
            while (!success && retries > 0) {
                try {
                    const translated = await translateBatch(sourceTexts, lang);
                    batch.forEach((key, idx) => {
                        targetKeysMap[key] = translated[idx] || sourceKeysMap[key];
                    });
                    success = true;
                    process.stdout.write('.');
                } catch (e) {
                    retries--;
                    process.stdout.write('!');
                    await delay(2000 * (4 - retries));
                }
            }
            await delay(500);
        }
    }

    // Reconstruct the nested object following sourceData structure
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
    console.log(`\n  - [${lang}] Saved.`);
}

async function run() {
    for (const lang of languages) {
        await processLanguage(lang);
    }
    console.log("ALL LANGUAGES DONE.");
}

run();
