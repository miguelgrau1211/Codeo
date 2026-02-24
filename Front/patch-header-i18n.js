/**
 * patch-header-i18n.js
 * Injects the HEADER namespace into every i18n JSON file with correct translations.
 */
const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'public', 'i18n');

const headers = {
    en: { DASHBOARD: 'Dashboard', STORY: 'Story Mode', INFINITE: 'Infinite Mode', LOGOUT: 'Log Out', LOGIN: 'Log In', REGISTER: 'Register' },
    fr: { DASHBOARD: 'Tableau de bord', STORY: 'Mode Histoire', INFINITE: 'Mode Infini', LOGOUT: 'Déconnexion', LOGIN: 'Connexion', REGISTER: "S'inscrire" },
    de: { DASHBOARD: 'Dashboard', STORY: 'Story-Modus', INFINITE: 'Endlosmodus', LOGOUT: 'Abmelden', LOGIN: 'Anmelden', REGISTER: 'Registrieren' },
    pt: { DASHBOARD: 'Painel', STORY: 'Modo História', INFINITE: 'Modo Infinito', LOGOUT: 'Sair', LOGIN: 'Entrar', REGISTER: 'Registrar' },
    ca: { DASHBOARD: 'Tauler', STORY: 'Mode Història', INFINITE: 'Mode Infinit', LOGOUT: 'Tancar sessió', LOGIN: 'Iniciar sessió', REGISTER: 'Registrar-se' },
    it: { DASHBOARD: 'Dashboard', STORY: 'Modalità Storia', INFINITE: 'Modalità Infinita', LOGOUT: 'Esci', LOGIN: 'Accedi', REGISTER: 'Registrati' },
    nl: { DASHBOARD: 'Dashboard', STORY: 'Verhaalmodus', INFINITE: 'Oneindige modus', LOGOUT: 'Uitloggen', LOGIN: 'Inloggen', REGISTER: 'Registreren' },
    ru: { DASHBOARD: 'Панель', STORY: 'Режим истории', INFINITE: 'Бесконечный режим', LOGOUT: 'Выйти', LOGIN: 'Войти', REGISTER: 'Регистрация' },
    zh: { DASHBOARD: '仪表板', STORY: '故事模式', INFINITE: '无限模式', LOGOUT: '退出登录', LOGIN: '登录', REGISTER: '注册' },
    tr: { DASHBOARD: 'Panel', STORY: 'Hikaye Modu', INFINITE: 'Sonsuz Mod', LOGOUT: 'Çıkış Yap', LOGIN: 'Giriş Yap', REGISTER: 'Kayıt Ol' },
    uk: { DASHBOARD: 'Панель', STORY: 'Режим Iсторії', INFINITE: 'Нескінченний режим', LOGOUT: 'Вийти', LOGIN: 'Увійти', REGISTER: 'Реєстрація' },
    ar: { DASHBOARD: 'لوحة القيادة', STORY: 'وضع القصة', INFINITE: 'الوضع اللانهائي', LOGOUT: 'تسجيل الخروج', LOGIN: 'تسجيل الدخول', REGISTER: 'التسجيل' },
    hi: { DASHBOARD: 'डैशबोर्ड', STORY: 'कहानी मोड', INFINITE: 'अनंत मोड', LOGOUT: 'लॉग आउट', LOGIN: 'लॉग इन', REGISTER: 'पंजीकरण' },
    val: { DASHBOARD: 'Tauler', STORY: 'Mode Història', INFINITE: 'Mode Infinit', LOGOUT: 'Tancar sessió', LOGIN: 'Iniciar sessió', REGISTER: 'Registrar-se' },
};

for (const [code, t] of Object.entries(headers)) {
    const file = path.join(dir, `${code}.json`);
    if (!fs.existsSync(file)) { console.log(`SKIP ${code}.json (not found)`); continue; }

    const json = JSON.parse(fs.readFileSync(file, 'utf8'));
    json.HEADER = t;
    fs.writeFileSync(file, JSON.stringify(json, null, 2), 'utf8');
    console.log(`✅  Patched ${code}.json`);
}

console.log('\nDone!');
