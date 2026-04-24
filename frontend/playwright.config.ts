import { defineConfig } from '@playwright/test';

const baseURL =
    (globalThis as { process?: { env?: Record<string, string | undefined> } }).process?.env?.PLAYWRIGHT_BASE_URL ||
    'http://127.0.0.1:4173';

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 30_000,
    expect: {
        timeout: 10_000,
    },
    use: {
        baseURL,
        trace: 'on-first-retry',
    },
    reporter: [['list']],
});
