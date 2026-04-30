import { test, expect } from '@playwright/test';

test.describe('Authentication Flow', () => {
    test('should login successfully as admin', async ({ page }) => {
        // Navigate to login page
        await page.goto('/auth/portal-sign-in');

        // Fill login form
        await page.fill('#email', 'admin@kdua.net');
        await page.fill('#password', 'admin123');

        // Click submit (ensure it's enabled after captcha check/validation)
        const submitBtn = page.locator('button[type="submit"]');
        await expect(submitBtn).toBeEnabled();
        await submitBtn.click();

        // Check if redirected to dashboard (/dash)
        await expect(page).toHaveURL(/.*dash/);

        // The title is localized, "Dashboard" is the default. 
        // We wait for the heading to appear.
        const heading = page.getByRole('heading', { level: 1 });
        await expect(heading).toBeVisible({ timeout: 10000 });
        // We'll be more flexible with the text
        await expect(heading).toHaveText(/(Dashboard|Beranda)/i);
    });

    test('should show error for invalid credentials', async ({ page }) => {
        await page.goto('/auth/portal-sign-in');

        await page.fill('#email', 'wrong@example.com');
        await page.fill('#password', 'wrongpassword');

        await page.click('button[type="submit"]');

        // Check for error message
        // It could be field-level or general
        const errorMsg = page.locator('text=/invalid|wrong|gagal/i').first();
        await expect(errorMsg).toBeVisible();

        // Take a screenshot if it fails (using automatic screenshot on failure is better, 
        // but we can manually take one for now if we want to see the state)
        // await page.screenshot({ path: 'test-results/login-failure.png' });
    });
});
