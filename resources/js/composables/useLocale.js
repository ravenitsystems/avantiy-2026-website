import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import router from '../router';

/**
 * Composable for locale and switching language.
 * Use t('your.key') from useI18n() in components for translated strings once keys are added.
 */
export function useLocale() {
    const { locale, t, te } = useI18n();

    const supportedLocales = computed(() => {
        return typeof window !== 'undefined' ? (window.__SUPPORTED_LOCALES || ['en']) : ['en'];
    });

    /** Navigate to the same path under a different locale (full page load). */
    function setLocale(newLocale) {
        if (!supportedLocales.value.includes(newLocale)) return;
        const path = router.currentRoute.value.fullPath;
        window.location.href = `/${newLocale}${path}`;
    }

    return {
        locale,
        supportedLocales,
        setLocale,
        t,
        te,
    };
}
