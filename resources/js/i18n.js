import { createI18n } from 'vue-i18n';

const locale = typeof window !== 'undefined' ? (window.__LOCALE || 'en') : 'en';
const supported = typeof window !== 'undefined' ? (window.__SUPPORTED_LOCALES || ['en']) : ['en'];
const fallbackLocale = 'en';

const messages = Object.fromEntries(
    supported.map((loc) => [loc, {}])
);

export const i18n = createI18n({
    legacy: false,
    locale,
    fallbackLocale,
    messages,
});

export default i18n;
