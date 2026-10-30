import { defineConfig, devices } from '@playwright/test';

/**
 * Browser checks for critical journeys and responsive layout.
 * Locally: run against an already-running seeded app (PLAYWRIGHT_BASE_URL) or let Playwright
 * start `php artisan serve`. CI provisions a seeded SQLite database first (see .github/workflows/ci.yml).
 */
const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://127.0.0.1:8123';

export default defineConfig({
    testDir: 'tests/browser',
    fullyParallel: false,
    workers: 1,
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',
    timeout: 45_000,
    use: {
        baseURL,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: [
        { name: 'desktop', use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 900 } } },
        { name: 'mobile', use: { ...devices['Pixel 7'], viewport: { width: 390, height: 844 } } },
    ],
    webServer: process.env.PLAYWRIGHT_BASE_URL
        ? undefined
        : {
              command: 'php artisan serve --host=127.0.0.1 --port=8123 --no-reload',
              url: `${baseURL}/up`,
              reuseExistingServer: true,
              timeout: 60_000,
          },
});
