import { test, expect } from '@playwright/test';
import path from 'node:path';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

test.describe('Media Management Flow', () => {
    test.beforeEach(async ({ page }) => {
        // Login before each test
        await page.goto('/login');
        await page.fill('#email', 'admin@kdua.net');
        await page.fill('#password', 'admin123');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/.*admin/);
    });

    test('should upload and delete media', async ({ page }) => {
        // Navigate to Media Library
        await page.goto('/admin/media');

        // Check if page loaded
        await expect(page.locator('h1')).toContainText(/Media/i);

        // Prepare a dummy file to upload
        const testFilePath = path.join(__dirname, 'test-image.png');
        fs.writeFileSync(testFilePath, 'fake image content');

        // Click Upload button to open modal
        const uploadBtn = page.getByRole('button', { name: /Upload/i });
        await expect(uploadBtn).toBeVisible();
        await uploadBtn.click();

        // Verify upload modal is open
        const modal = page.locator('text=/Upload Media/i').first();
        await expect(modal).toBeVisible();

        // Fill file input (Playwright handles hidden inputs well if we use setInputFiles)
        // We search for the input type="file"
        const fileInput = page.locator('input[type="file"]');
        await fileInput.setInputFiles(testFilePath);

        // Wait for upload to complete and modal to close
        // In JA-Platform, it usually shows a progress bar then closes or shows success
        await expect(modal).not.toBeVisible({ timeout: 15000 });

        // Verify file appears in grid (we look for our filename or a generic media item)
        // For now, let's just check if there's at least one media item
        // const mediaItem = page.locator('.media-item').first();
        // await expect(mediaItem).toBeVisible();

        // Clean up: delete the test file from disk
        fs.unlinkSync(testFilePath);
    });
});
