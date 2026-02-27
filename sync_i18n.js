
const fs = require('fs');
const path = require('path');

const i18nDir = 'c:\\Users\\Haziel\\Desktop\\2_DAW\\Proyecto\\Front\\public\\i18n';
const sourceFile = path.join(i18nDir, 'es.json');

const sourceData = JSON.parse(fs.readFileSync(sourceFile, 'utf8'));

const translations = {
    "en": {
        "SIDEBAR.HOME": "Home",
        "SIDEBAR.STORY_MODE": "Story Mode",
        "SIDEBAR.ROGUELIKE": "Roguelike",
        "SIDEBAR.SHOP": "Shop",
        "SIDEBAR.RANKING": "Ranking",
        "SIDEBAR.SUPPORT": "Support",
        "SIDEBAR.SETTINGS": "Settings",
        "SIDEBAR.PROFILE": "Profile",
        "DASHBOARD.NAV.SUPPORT": "Support",
        "LANDING.BADGE": "Gamified learning platform",
        "LANDING.FOOTER_SUPPORT": "Support",
        "ACHIEVEMENTS": {
            "BACK": "Main Menu",
            "TITLE": "Achievements",
            "LEGENDARY": "Legendary",
            "SUBTITLE": "Master the code, unlock badges and build your legacy in the global ranking.",
            "PROGRESS": "Progress",
            "UNLOCKED": "Unlocked",
            "COMPLETED": "Completed",
            "TOTAL": "Total Achievements",
            "FILTER_ALL": "All",
            "FILTER_UNLOCKED": "Unlocked",
            "FILTER_LOCKED": "Locked",
            "RETRY": "Retry",
            "ERROR_LOADING": "Achievements could not be loaded. Please try again.",
            "EMPTY": "No achievements in this category.",
            "PROGRESS_DETAIL": "You have completed {{obtained}} of {{total}} achievements",
            "UNLOCKED_STATUS": "Unlocked",
            "LOCKED_STATUS": "Locked",
            "RARITY_SPECIAL": "Special",
            "RARITY_RARE": "Rare",
            "RARITY_EPIC": "Epic",
            "RARITY_LEGENDARY": "Legendary",
            "RARITY_CELESTIAL": "Celestial"
        },
        "SUPPORT": {
            "BACK": "Back to start",
            "TITLE_1": "Support",
            "TITLE_2": "Center",
            "SUBTITLE": "Found a bug in the Matrix? Your code won't compile and you don't know why? We're here to help.",
            "FORM_TITLE": "Report an Issue",
            "FORM_SUBTITLE": "Help us improve Codeo",
            "EMAIL_LABEL": "Contact Email",
            "EMAIL_PH": "your@email.com",
            "CATEGORY": "Category",
            "CAT_BUG": "Error / Bug",
            "CAT_SUGGESTION": "Suggestion",
            "CAT_FEEDBACK": "Feedback",
            "CAT_OTHER": "Others",
            "SUBJECT": "Subject",
            "SUBJECT_PH": "Ex: I can't see my achievements in profile",
            "DESCRIPTION": "Detailed description",
            "DESCRIPTION_PH": "Describe what happened, what you expected and steps to reproduce...",
            "SUCCESS": "Report sent successfully. Thanks!",
            "ERROR": "Error sending report. Please try again.",
            "TIP_TITLE": "Pro Tip:",
            "TIP_DESC": "Detailed and constructive reports help improve the platform for everyone. Best contributions are rewarded with XP!",
            "SPAM_WARNING": "⚠️ Security Note:",
            "SPAM_DESC": "System abuse or spamming false reports will result in a penalty of -100 XP.",
            "SENDING": "Sending...",
            "SUBMIT": "Submit Report",
            "COMMUNITY": "Direct Community",
            "LIVE_CHAT": "Live chat",
            "REPORT_BUG": "Report Bug",
            "FAQ_TITLE": "Frequently Asked Questions",
            "CLICK_TO_SEE": "Click to see",
            "FAQS": {
                "Q1": "How long does it take to respond?",
                "A1": "Our team reviews reports daily. Usually you get a response in 24-48 business hours.",
                "Q2": "How can I reset my progress?",
                "A2": "Go to Settings > Danger Zone > Reset Progress. Be careful, this can't be undone.",
                "Q3": "Do I get anything for reporting bugs?",
                "A3": "Yes! If the bug is validated, you'll receive credits and XP as a reward.",
                "Q4": "Can I contribute to the code?",
                "A4": "Currently the core is private, but you can contribute by reporting issues or creating content."
            }
        },
        "DASHBOARD.PAYMENT_INIT_TITLE": "Initializing Secure Payment",
        "DASHBOARD.PAYMENT_INIT_DESC": "Connecting to the payment gateway...",
        "DASHBOARD.CARD_DATA": "Card Details",
        "DASHBOARD.CARD_HOLDER_PH": "HOLDER NAME",
        "DASHBOARD.SEASON_NAME": "ORIGINS"
    },
    "ca": {
        "SIDEBAR.HOME": "Inici",
        "SIDEBAR.STORY_MODE": "Modo Història",
        "SIDEBAR.ROGUELIKE": "Roguelike",
        "SIDEBAR.SHOP": "Botiga",
        "SIDEBAR.RANKING": "Ranking",
        "SIDEBAR.SUPPORT": "Soport",
        "SIDEBAR.SETTINGS": "Ajustos",
        "SIDEBAR.PROFILE": "Perfil",
        "DASHBOARD.NAV.SUPPORT": "Soport",
        "LANDING.BADGE": "Plataforma d'aprenentatge gamificada",
        "LANDING.FOOTER_SUPPORT": "Soport",
        "ACHIEVEMENTS": {
            "BACK": "Menú Principal",
            "TITLE": "Assoliments",
            "LEGENDARY": "Llegendaris",
            "SUBTITLE": "Domina el codi, desbloqueja insignies i construeix el teu llegat al rànquing global.",
            "PROGRESS": "Progrés",
            "UNLOCKED": "Desbloquejats",
            "COMPLETED": "Completat",
            "TOTAL": "Total Assoliments",
            "FILTER_ALL": "Tots",
            "FILTER_UNLOCKED": "Desbloquejats",
            "FILTER_LOCKED": "Bloquejats",
            "RETRY": "Reintentar",
            "ERROR_LOADING": "No s'han pogut carregar els assoliments. Torna-ho a provar.",
            "EMPTY": "No hi ha assoliments en aquesta categoria.",
            "PROGRESS_DETAIL": "Has completat {{obtained}} de {{total}} assoliments",
            "UNLOCKED_STATUS": "Desbloquejat",
            "LOCKED_STATUS": "Bloquejat",
            "RARITY_SPECIAL": "Especial",
            "RARITY_RARE": "Rar",
            "RARITY_EPIC": "Èpic",
            "RARITY_LEGENDARY": "Llegendari",
            "RARITY_CELESTIAL": "Celestial"
        },
        "SUPPORT": {
            "BACK": "Tornar a l'inici",
            "TITLE_1": "Centre de",
            "TITLE_2": "Soport",
            "SUBTITLE": "Has trobat un error a la Matrix? El teu codi no compila i no saps per què? Estem aquí per ajudar-te.",
            "FORM_TITLE": "Informar d'un Problema",
            "FORM_SUBTITLE": "Ajuda'ns a millorar Codeo",
            "EMAIL_LABEL": "Email de contacte",
            "EMAIL_PH": "el teu@email.com",
            "CATEGORY": "Categoria",
            "CAT_BUG": "Error / Bug",
            "CAT_SUGGESTION": "Suggeriment",
            "CAT_FEEDBACK": "Feedback",
            "CAT_OTHER": "Altres",
            "SUBJECT": "Assumpte",
            "SUBJECT_PH": "Ex: No puc veure els assoliments al perfil",
            "DESCRIPTION": "Descripció detallada",
            "DESCRIPTION_PH": "Descriu què ha passat, què esperaves i els passos per reproduir-ho...",
            "SUCCESS": "Informe enviat amb èxit. Gràcies!",
            "ERROR": "S'ha produït un error en enviar l'informe. Torna-ho a provar.",
            "TIP_TITLE": "Tip Pro:",
            "TIP_DESC": "Els informes detallats i constructius ajuden a millorar la plataforma per a tothom. Les millors contribucions són recompensades amb XP!",
            "SPAM_WARNING": "⚠️ Avís de Seguretat:",
            "SPAM_DESC": "L'abús del sistema o l'enviament d'informes falsos (SPAM) comporta una penalització de -100 XP.",
            "SENDING": "Enviant...",
            "SUBMIT": "Enviar Informe",
            "COMMUNITY": "Comunitat Directa",
            "LIVE_CHAT": "Xat en viu",
            "REPORT_BUG": "Informar d'un error",
            "FAQ_TITLE": "Preguntes Freqüents",
            "CLICK_TO_SEE": "Fes clic per veure",
            "FAQS": {
                "Q1": "Quant es triga a respondre?",
                "A1": "El nostre equip revisa els informes diàriament. Generalment reps resposta en 24-48 hores laborables.",
                "Q2": "Com puc reiniciar el meu progrés?",
                "A2": "Ves a Ajustos > Zona de Perill > Reiniciar Progrés. Compte, aquesta acció no es pot desfer.",
                "Q3": "Guanyo alguna cosa per informar d'errors?",
                "A3": "Sí! Si l'error es valida, rebràs crèdits i XP com a recompensa.",
                "Q4": "Puc contribuir al codi?",
                "A4": "Actualment el nucli és privat, però pots contribuir informant de problemes o creant contingut."
            }
        },
        "DASHBOARD.PAYMENT_INIT_TITLE": "Iniciant Pagament Segur",
        "DASHBOARD.PAYMENT_INIT_DESC": "Connectant amb la passarel·la de pagaments...",
        "DASHBOARD.CARD_DATA": "Dades de la Targeta",
        "DASHBOARD.CARD_HOLDER_PH": "NOM DEL TITULAR",
        "DASHBOARD.SEASON_NAME": "ORÍGENS"
    },
    "fr": {
        "ACHIEVEMENTS.ERROR_LOADING": "Impossible de charger les succès. Veuillez réessayer.",
        "ACHIEVEMENTS.PROGRESS_DETAIL": "Vous avez complété {{obtained}} sur {{total}} succès",
        "DASHBOARD.PAYMENT_INIT_TITLE": "Initialisation du paiement sécurisé",
        "DASHBOARD.PAYMENT_INIT_DESC": "Connexion à la passerelle de paiement...",
        "DASHBOARD.CARD_DATA": "Détails de la carte",
        "DASHBOARD.CARD_HOLDER_PH": "NOM DU TITULAIRE",
        "DASHBOARD.SEASON_NAME": "ORIGINES"
    },
    "de": {
        "ACHIEVEMENTS.ERROR_LOADING": "Erfolge konnten nicht geladen werden. Bitte versuchen Sie es erneut.",
        "ACHIEVEMENTS.PROGRESS_DETAIL": "Sie haben {{obtained}} von {{total}} Erfolgen abgeschlossen",
        "DASHBOARD.PAYMENT_INIT_TITLE": "Sichere Zahlung wird initialisiert",
        "DASHBOARD.PAYMENT_INIT_DESC": "Verbindung zum Zahlungsgateway wird hergestellt...",
        "DASHBOARD.CARD_DATA": "Kartendetails",
        "DASHBOARD.CARD_HOLDER_PH": "NAME DES KARTENINHABERS",
        "DASHBOARD.SEASON_NAME": "URSPRÜNGE"
    },
    "it": {
        "ACHIEVEMENTS.ERROR_LOADING": "Impossibile caricare gli obiettivi. Riprova.",
        "ACHIEVEMENTS.PROGRESS_DETAIL": "Hai completato {{obtained}} di {{total}} obiettivi",
        "DASHBOARD.PAYMENT_INIT_TITLE": "Inizializzazione pagamento sicuro",
        "DASHBOARD.PAYMENT_INIT_DESC": "Connessione al gateway di pagamento...",
        "DASHBOARD.CARD_DATA": "Dettagli della carta",
        "DASHBOARD.CARD_HOLDER_PH": "NOME DEL TITOLARE",
        "DASHBOARD.SEASON_NAME": "ORIGINI"
    },
    "pt": {
        "ACHIEVEMENTS.ERROR_LOADING": "Não foi possível carregar as conquistas. Tente novamente.",
        "ACHIEVEMENTS.PROGRESS_DETAIL": "Você completou {{obtained}} de {{total}} conquistas",
        "DASHBOARD.PAYMENT_INIT_TITLE": "Inicializando pagamento seguro",
        "DASHBOARD.PAYMENT_INIT_DESC": "Conectando ao gateway de pagamento...",
        "DASHBOARD.CARD_DATA": "Detalhes do cartão",
        "DASHBOARD.CARD_HOLDER_PH": "NOME DEL TITULAR",
        "DASHBOARD.SEASON_NAME": "ORIGENS"
    },
    "ru": {
        "ACHIEVEMENTS.ERROR_LOADING": "Не удалось загрузить достижения. Пожалуйста, попробуйте еще раз.",
        "ACHIEVEMENTS.PROGRESS_DETAIL": "Вы выполнили {{obtained}} из {{total}} достижений",
        "DASHBOARD.PAYMENT_INIT_TITLE": "Инициализация безопасного платежа",
        "DASHBOARD.PAYMENT_INIT_DESC": "Подключение к платежному шлюзу...",
        "DASHBOARD.CARD_DATA": "Данные карты",
        "DASHBOARD.CARD_HOLDER_PH": "ИМЯ ВЛАДЕЛЬЦА КАРТЫ",
        "DASHBOARD.SEASON_NAME": "ИСТОКИ"
    },
    "hi": {
        "LANDING.BADGE": "गेमिफाइड लर्निंग प्लेटफॉर्म",
        "LANDING.FOOTER_SUPPORT": "सहायता"
    },
    "ar": {
        "LANDING.BADGE": "منصة تعلم محفزة",
        "LANDING.FOOTER_SUPPORT": "الدعم"
    },
    "zh": {
        "LANDING.BADGE": "游戏化学习平台",
        "LANDING.FOOTER_SUPPORT": "支持"
    }
    // ... adding more if needed, but these cover most common ones.
};

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
const files = fs.readdirSync(i18nDir).filter(f => f.endsWith('.json') && f !== 'es.json');

files.forEach(filename => {
    const lang = filename.split('.')[0];
    const filePath = path.join(i18nDir, filename);
    const targetData = JSON.parse(fs.readFileSync(filePath, 'utf8'));
    const targetKeysMap = getAllKeys(targetData);

    let updated = 0;
    for (const key in sourceKeysMap) {
        let value = sourceKeysMap[key];

        // If we have a specific translation for this key and language
        if (translations[lang] && translations[lang][key]) {
            value = translations[lang][key];
        } else if (lang === 'en' || lang === 'ca') {
            // Already handled manually or skip if not missing
        }

        const parts = key.split('.');
        let current = targetData;
        for (let i = 0; i < parts.length - 1; i++) {
            if (!current[parts[i]]) {
                current[parts[i]] = {};
            }
            current = current[parts[i]];
        }

        // If missing or (if it was added by previous script as Spanish placeholder)
        if (!targetKeysMap.hasOwnProperty(key) || (targetKeysMap[key] === sourceKeysMap[key] && lang !== 'es' && translations[lang] && translations[lang][key])) {
            current[parts[parts.length - 1]] = value;
            updated++;
        }
    }

    if (updated > 0) {
        console.log(`File ${filename}: Updated ${updated} keys.`);
        fs.writeFileSync(filePath, JSON.stringify(targetData, null, 2), 'utf8');
    }
});
