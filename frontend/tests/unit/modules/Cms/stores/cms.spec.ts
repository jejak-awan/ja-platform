import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import api from '@/engine/api/client';

vi.mock('@/engine/api/client', () => ({
    default: {
        get: vi.fn(),
        put: vi.fn(),
    }
}));

// Mock logger to avoid console spam
vi.mock('@/shared/utils/logger', () => ({
    logger: {
        error: vi.fn(),
        debug: vi.fn(),
    }
}));

describe('CMS Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
        vi.useFakeTimers();

        // Mock matchMedia
        const listeners: any[] = [];
        Object.defineProperty(window, 'matchMedia', {
            writable: true,
            value: vi.fn().mockImplementation(query => ({
                matches: false,
                media: query,
                onchange: null,
                addListener: vi.fn(),
                removeListener: vi.fn(),
                addEventListener: vi.fn((type, cb) => {
                    if (type === 'change') listeners.push(cb);
                }),
                removeEventListener: vi.fn(),
                dispatchEvent: vi.fn(),
            })),
        });
        (window as any)._matchMediaListeners = listeners;

        localStorage.clear();
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('fetches settings group', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockResolvedValueOnce({ data: { max_upload_size: 2048 } });

        const result = await store.fetchSettingsGroup('media');

        expect(api.get).toHaveBeenCalledWith('/admin/cms/settings/group/media');
        expect(store.settings.max_upload_size).toBe(2048);
        expect(result.max_upload_size).toBe(2048);
    });

    it('prevents concurrent duplicate settings fetches', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockImplementation(() => new Promise(resolve => setTimeout(() => resolve({ data: { a: 1 } }), 100)));

        const p1 = store.fetchSettingsGroup('test');
        const p2 = store.fetchSettingsGroup('test');

        vi.advanceTimersByTime(101);
        await Promise.all([p1, p2]);
        expect(api.get).toHaveBeenCalledTimes(1);
    });

    it('handles settings fetch error', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockRejectedValueOnce(new Error('API Err'));

        const res = await store.fetchSettingsGroup('err');
        expect(res).toEqual({});
    });

    it('fetches public settings', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockResolvedValueOnce({ data: { data: { site_name: 'Test' } } });

        const res = await store.fetchPublicSettings();
        expect(store.siteSettings.site_name).toBe('Test');
        expect(res.site_name).toBe('Test');
    });

    it('handles public settings fetch error', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockRejectedValueOnce(new Error('API Err'));

        const res = await store.fetchPublicSettings();
        expect(res).toEqual(store.siteSettings);
    });

    it('fetches contents', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockResolvedValueOnce({ data: { data: [{ id: 1 }], pagination: {} } });

        const result = await store.fetchContents({ page: 1 });
        expect(api.get).toHaveBeenCalledWith('/ja/contents', { params: { page: 1 } });
        expect(store.contents).toHaveLength(1);
        expect(result.data).toHaveLength(1);
    });

    it('handles contents fetch error', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockRejectedValueOnce(new Error('fail'));

        await store.fetchContents();
        expect(store.contents).toEqual([]);
    });

    it('fetches single content', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockResolvedValueOnce({ data: { title: 'Hello' } });

        const res = await store.fetchContent('hello');
        expect(api.get).toHaveBeenCalledWith('/ja/contents/hello');
        expect(store.currentContent).toEqual({ title: 'Hello' });
        expect(res).toEqual({ title: 'Hello' });
    });

    it('handles single content fetch error', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockRejectedValueOnce(new Error('fail'));

        const res = await store.fetchContent('hello');
        expect(res).toBeNull();
    });

    it('fetches categories', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockResolvedValueOnce({ data: { data: [{ id: 1 }] } });

        const res = await store.fetchCategories();
        expect(store.categories).toHaveLength(1);
        expect(res).toHaveLength(1);
    });

    it('handles categories fetch error', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockRejectedValueOnce(new Error('err'));

        const res = await store.fetchCategories();
        expect(store.categories).toEqual([]);
        expect(res).toEqual([]);
    });

    it('fetches tags', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockResolvedValueOnce({ data: { data: [{ id: 1 }] } });

        const res = await store.fetchTags();
        expect(store.tags).toHaveLength(1);
        expect(res).toHaveLength(1);
    });

    it('handles tags fetch error', async () => {
        const store = useCmsStore();
        vi.mocked(api.get).mockRejectedValueOnce(new Error('err'));

        const res = await store.fetchTags();
        expect(store.tags).toEqual([]);
        expect(res).toEqual([]);
    });

    it('initializes theme from local storage', async () => {
        const store = useCmsStore();
        localStorage.setItem('admin-dark-mode', 'dark');

        await store.initTheme();
        expect(store.themeMode).toBe('dark');
        expect(store.isDarkMode).toBe(true);
        expect(document.documentElement.classList.contains('dark')).toBe(true);

        // Test matchMedia listener (Line 148-150 coverage)
        const listeners = (window as any)._matchMediaListeners;
        if (listeners && listeners.length > 0) {
            store.themeMode = 'system';
            listeners[0]({ matches: false });
            expect(store.isDarkMode).toBe(false);
            listeners[0]({ matches: true });
            expect(store.isDarkMode).toBe(true);
        }
    });

    it('syncs theme with backend', async () => {
        const store = useCmsStore();
        localStorage.setItem('user', JSON.stringify({ id: 1, name: 'Test User' }));
        vi.mocked(api.put).mockResolvedValueOnce({});

        store.setThemeMode('light');
        expect(api.put).toHaveBeenCalledWith('/profile/preferences', { dark_mode: 'light' });

        // Coverage for setTimeout (no-transitions, Line 204)
        expect(document.documentElement.classList.contains('no-transitions')).toBe(true);
        vi.advanceTimersByTime(51);
        expect(document.documentElement.classList.contains('no-transitions')).toBe(false);
    });

    it('syncs theme with backend failure handled gracefully', async () => {
        const store = useCmsStore();
        localStorage.setItem('user', JSON.stringify({ id: 1, name: 'Test User' }));
        vi.mocked(api.put).mockRejectedValueOnce(new Error('network'));

        await store.syncThemeWithBackend('light');
    });

    it('loads theme preferences from backend', async () => {
        const store = useCmsStore();
        localStorage.setItem('user', JSON.stringify({ id: 1, name: 'Test User' }));
        vi.mocked(api.get).mockResolvedValueOnce({ data: { success: true, data: { dark_mode: 'dark' } } });

        store.themeMode = 'light';
        await store.loadThemePreferences();
        expect(store.themeMode).toBe('dark');
    });

    it('loads theme preferences from backend failure handled', async () => {
        const store = useCmsStore();
        localStorage.setItem('user', JSON.stringify({ id: 1, name: 'Test User' }));
        vi.mocked(api.get).mockRejectedValueOnce(new Error('network'));

        store.themeMode = 'light';
        await store.loadThemePreferences();
        expect(store.themeMode).toBe('light'); // Unchanged
    });

    it('toggles dark mode', () => {
        const store = useCmsStore();
        store.isDarkMode = false;

        store.toggleDarkMode();
        expect(store.themeMode).toBe('dark');

        store.toggleDarkMode(false);
        expect(store.themeMode).toBe('light');
    });
});
