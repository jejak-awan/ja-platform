import { defineConfig } from '@playwright/test';

const procEnv = (globalThis as { process?: { env?: Record<string, string | undefined> } }).process?.env;

const baseURL = procEnv?.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:4173';

const enableWebServer = procEnv?.PLAYWRIGHT_WEB_SERVER === '1';

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
    ...(enableWebServer
        ? {
              webServer: {
                  command: 'npm run preview -- --host 127.0.0.1 --port 4173',
                  url: baseURL,
                  reuseExistingServer: true,
                  timeout: 120_000,
              },
          }
        : {}),
});
