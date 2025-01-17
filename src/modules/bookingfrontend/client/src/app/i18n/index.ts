import {createInstance, i18n} from 'i18next';
import {initReactI18next} from 'react-i18next/initReactI18next';
import {cookieName, defaultNS, fallbackLng, getOptions, getTranslationURL, ILanguage, languages} from './settings';

// Simple in-memory cache for translations
const i18nCache = new Map<string, i18n>();

const initI18next = async (lng: ILanguage, ns?: string | string[]): Promise<i18n> => {
    const i18nInstance = createInstance();
    await i18nInstance
        .use(initReactI18next)
        .init(getOptions(lng, ns));

    // Fetch translations from the API
    const translationUrl = getTranslationURL(lng);
    const response = await fetch(translationUrl);
    const translations: Record<string, string> = await response.json();

    // Add the fetched resources to i18next
    i18nInstance.addResourceBundle(lng.key, (Array.isArray(ns) ? ns[0] : ns) as any, translations, true, true);

    return i18nInstance;
};

export async function getTranslation(lng?: string, ns?: string | string[]): Promise<{
    t: (key: string) => string,
    i18n: i18n
}> {
    let choosenLngString = lng;

    if(!choosenLngString && typeof window === 'undefined') {

        const cookies = require("next/headers").cookies
        const cookieStore = cookies();
        choosenLngString = cookieStore.get(cookieName as any)?.value
    }


    let language = languages.find(e => e.key === choosenLngString);

    if(!language) {
        language = fallbackLng;
    }

    // Check cache for the specific language and namespace combination
    const cacheKey = `${language.key}-${Array.isArray(ns) ? ns.join(',') : ns || defaultNS}`;
    if (i18nCache.has(cacheKey)) {
        // Return cached instance if available
        const cachedInstance = i18nCache.get(cacheKey)!;
        return {
            t: cachedInstance.getFixedT(language.key as any, (Array.isArray(ns) ? ns[0] : ns) as any),
            i18n: cachedInstance
        };
    }

    const i18nextInstance = await initI18next(language, ns || defaultNS);
    i18nCache.set(cacheKey, i18nextInstance); // Store instance in cache
    return {
        t: i18nextInstance.getFixedT(language.key as any, (Array.isArray(ns) ? ns[0] : ns) as any),
        i18n: i18nextInstance
    };
}