import { computed, ref } from 'vue';
import { useSystemStore } from '@/modules/System/stores/system';
import { SECURITY_ROUTES } from '@/config/security';

type ThemeMode = 'light' | 'dark' | 'system'
type DarkModeScope = 'admin' | 'frontend'

const FRONTEND_THEME_KEY = 'frontend-dark-mode'
const frontendThemeMode = ref<ThemeMode>('system')
const frontendIsDarkMode = ref(false)
let frontendInitialized = false

const resolveIsDark = (mode: ThemeMode) => {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    return mode === 'dark' || (mode === 'system' && prefersDark)
}

const initFrontendTheme = () => {
    if (frontendInitialized) return
    const savedMode = localStorage.getItem(FRONTEND_THEME_KEY)
    const parsedMode: ThemeMode = savedMode === 'light' || savedMode === 'dark' || savedMode === 'system'
        ? savedMode
        : 'system'
    frontendThemeMode.value = parsedMode
    frontendIsDarkMode.value = resolveIsDark(parsedMode)
    frontendInitialized = true
}

/**
 * Keep `document.documentElement` dark class in sync with the *current* route:
 * admin (`/dash`) uses CMS store + `admin-dark-mode`; everything else uses frontend prefs + `frontend-dark-mode`.
 * Call this on every SPA navigation so leaving admin does not leave `dark` stuck from the dashboard.
 */
export function syncDocumentDarkClassForRoute(path: string) {
    const isAdmin = path.startsWith(SECURITY_ROUTES.dashboardBase)
    if (isAdmin) {
        const systemStore = useSystemStore()
        const raw = localStorage.getItem('admin-dark-mode') || 'system'
        const mode: ThemeMode = raw === 'light' || raw === 'dark' || raw === 'system' ? raw : 'system'
        if (systemStore.themeMode !== mode) {
            systemStore.setThemeMode(mode, false)
        } else {
            systemStore.applyThemeToDocument()
        }
        return
    }

    initFrontendTheme()
    frontendIsDarkMode.value = resolveIsDark(frontendThemeMode.value)
    if (frontendIsDarkMode.value) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
}

export function useDarkMode(scope: DarkModeScope = 'admin') {
    const systemStore = useSystemStore();
    if (scope === 'frontend') {
        initFrontendTheme()
    }

    const isDark = computed(() => scope === 'admin' ? systemStore.isDarkMode : frontendIsDarkMode.value);
    const actualMode = computed(() => isDark.value ? 'dark' : 'light');
    const currentMode = computed(() => scope === 'admin' ? systemStore.themeMode : frontendThemeMode.value);

    const setMode = (mode: ThemeMode) => {
        if (scope === 'admin') {
            systemStore.setThemeMode(mode);
            return;
        }

        frontendThemeMode.value = mode
        frontendIsDarkMode.value = resolveIsDark(mode)
        localStorage.setItem(FRONTEND_THEME_KEY, mode)
        if (frontendIsDarkMode.value) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    };

    const toggleMode = () => {
        if (scope === 'admin') {
            systemStore.toggleDarkMode();
            return;
        }

        const nextMode: ThemeMode = frontendIsDarkMode.value ? 'light' : 'dark'
        setMode(nextMode)
    };

    return {
        currentMode,
        actualMode,
        isDark,
        setMode,
        toggleMode,
        modes: {
            LIGHT: 'light',
            DARK: 'dark',
            SYSTEM: 'system'
        },
        loadFromBackend: () => scope === 'admin' ? systemStore.loadThemePreferences() : Promise.resolve(),
    };
}
