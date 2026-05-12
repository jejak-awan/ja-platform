import { createI18n } from 'vue-i18n';
import config, { type LocaleConfig } from '../locales/config';
import type { Ref } from 'vue';
import en from '../locales/en';
import id from '../locales/id';

/**
 * Detect the best locale to use
 * Priority: 1. localStorage, 2. Browser language, 3. Default
 */
const detectLocale = (): string => {
    // 1. Check localStorage first (user preference)
    const savedLocale = localStorage.getItem('locale');
    if (savedLocale && config.availableLocales.some((l: LocaleConfig) => l.code === savedLocale)) {
        return savedLocale;
    }

    // 2. Try to detect from browser
    const browserLang = navigator.language || (navigator as unknown as { userLanguage: string }).userLanguage;
    if (browserLang) {
        // Try exact match first (e.g., 'id-ID' -> 'id')
        const segments = browserLang.split('-');
        const langCode = (segments[0] || '').toLowerCase();
        if (config.availableLocales.some((l: LocaleConfig) => l.code === langCode)) {
            return langCode;
        }
    }

    // 3. Fallback to default
    return config.locale;
};

const detectedLocale = detectLocale();

// Messages now include builder translations directly from main lang
// No need for separate merge - builder is exported at root level in lang/*/index.ts
const messages = {
    en,
    id
};

const i18n = createI18n({
    ...config,
    locale: detectedLocale,
    messages: messages,
});

// Save detected locale to localStorage for next visit
if (!localStorage.getItem('locale')) {
    localStorage.setItem('locale', detectedLocale);
}

export default i18n;

// Helper function to switch language
export const setLocale = (locale: string) => {
    if (i18n.global) {
        (i18n.global.locale as unknown as Ref<string>).value = locale;
    }
    localStorage.setItem('locale', locale);
    document.documentElement.lang = locale;
};

// Get current locale
export const getLocale = () => (i18n.global.locale as unknown as Ref<string>).value;

// Get available locales
export const getAvailableLocales = () => config.availableLocales;

// Get detected browser locale (for debugging)
export const getBrowserLocale = () => {
    const browserLang = navigator.language || (navigator as unknown as { userLanguage: string }).userLanguage;
    const segments = browserLang ? browserLang.split('-') : [];
    return segments[0] ? segments[0].toLowerCase() : null;
};
