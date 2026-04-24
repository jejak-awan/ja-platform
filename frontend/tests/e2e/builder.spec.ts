import { test, expect } from '@playwright/test';

test.describe('Content Builder Flow', () => {
    test.beforeEach(async ({ page }) => {
        // Login before each test
        await page.goto('/auth/portal-sign-in');
        await page.fill('#email', 'admin@kdua.net');
        await page.fill('#password', 'admin123');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/.*dash/);
    });

    test('should create new content using builder', async ({ page }) => {
        // Go to Content Studio
        await page.goto('/dash/studio');

        // Click Create New
        // We use text search because the button text is localized but usually "Create New" or has a Plus icon
        const createBtn = page.getByRole('button', { name: /Create New|Buat Baru/i });
        await expect(createBtn).toBeVisible();
        await createBtn.click();

        // Fill Title
        await page.fill('input[placeholder*="title" i]', 'E2E Test Content');
        // Press Tab or Click outside to ensure any reactive triggers fire
        await page.keyboard.press('Tab');

        // The editor defaults to 'Classic' mode in this setup.
        // We need to click the 'Builder' toggle button.
        // The button has text 'builder' and is inside the toggle container.
        const builderToggleBtn = page.locator('button').filter({ hasText: /^builder$/i });
        await expect(builderToggleBtn).toBeVisible({ timeout: 10000 });
        await builderToggleBtn.click();

        // Verify Builder is loaded
        const builder = page.locator('.ja-builder-main');
        await expect(builder).toBeVisible({ timeout: 10000 });

        // Save content
        const saveBtn = page.locator('button:has-text("Save"), button:has-text("Simpan")').first();
        await expect(saveBtn).toBeVisible();
        await saveBtn.click();

        // Verify success toast/message (Check for common toast patterns)
        // In shadcn/toast it usually has role="status" or "alert"
        // await expect(page.locator('text=/Success|Berhasil/i')).toBeVisible();
    });
});
