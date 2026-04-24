import { describe, it, expect, vi, beforeEach } from 'vitest';
import { enhanceCodeBlocks } from '@/utils/code-block-enhancer';
import { logger } from '@/utils/logger';

vi.mock('@/utils/logger');
vi.mock('@/services/api'); // Circular dep via logger

describe('Code Block Enhancer', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div class="article-content">
                <pre><code>const x = 1;\nconst y = 2;</code></pre>
            </div>
            <div class="other-content">
                <pre><code>should not be enhanced</code></pre>
            </div>
        `;
        vi.clearAllMocks();
        vi.useFakeTimers();

        // Mock navigator.clipboard
        Object.defineProperty(navigator, 'clipboard', {
            value: {
                writeText: vi.fn().mockResolvedValue(undefined),
            },
            configurable: true,
            writable: true
        });
    });

    it('adds copy button and line numbers to article content pre blocks', () => {
        enhanceCodeBlocks();

        const pre = document.querySelector('.article-content pre') as HTMLElement;
        expect(pre?.querySelector('.copy-btn')).toBeTruthy();
        expect(pre?.dataset.enhanced).toBe('true');

        const lines = pre?.querySelectorAll('.line');
        expect(lines?.length).toBe(2);
        expect(lines?.[0]?.textContent).toBe('const x = 1;');
    });

    it('does not enhance blocks outside article-content', () => {
        enhanceCodeBlocks();

        const pre = document.querySelector('.other-content pre') as HTMLElement;
        expect(pre?.querySelector('.copy-btn')).toBeFalsy();
        expect(pre?.dataset.enhanced).toBeUndefined();
    });

    it('prevents double enhancement', () => {
        enhanceCodeBlocks();
        const firstBtn = document.querySelector('.copy-btn');

        enhanceCodeBlocks();
        const btns = document.querySelectorAll('.copy-btn');
        expect(btns.length).toBe(1);
        expect(btns[0]).toBe(firstBtn);
    });

    it('handles copy button click', async () => {
        enhanceCodeBlocks();
        const copyBtn = document.querySelector('.copy-btn') as HTMLButtonElement;

        copyBtn.click();

        expect(navigator.clipboard.writeText).toHaveBeenCalledWith('const x = 1;\nconst y = 2;');

        // Wait for the .then() microtask
        await Promise.resolve();
        await Promise.resolve(); // Extra tick just in case

        expect(copyBtn.classList.contains('copied')).toBe(true);
        expect(copyBtn.textContent).toContain('Copied!');

        // Now advance timers to see it reset
        vi.advanceTimersByTime(2001);
        expect(copyBtn.classList.contains('copied')).toBe(false);
        expect(copyBtn.textContent).toContain('Copy');
    });

    it('handles copy error', async () => {
        const error = new Error('Clipboard failed');
        (navigator.clipboard.writeText as any).mockRejectedValueOnce(error);

        enhanceCodeBlocks();
        const copyBtn = document.querySelector('.copy-btn') as HTMLButtonElement;

        copyBtn.click();

        await vi.runAllTimersAsync();

        expect(logger.error).toHaveBeenCalledWith('Failed to copy:', error);
    });

    it('preserves syntax highlighting if present', () => {
        document.body.innerHTML = `
            <div class="article-content">
                <pre><code class="hljs"><span class="hljs-keyword">const</span> x = <span class="hljs-number">1</span>;</code></pre>
            </div>
        `;

        enhanceCodeBlocks();

        const code = document.querySelector('code');
        expect(code?.querySelector('.hljs-keyword')).toBeTruthy();
        expect(code?.querySelector('.line')).toBeTruthy();
    });
});
