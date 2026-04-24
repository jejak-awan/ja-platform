import { logger } from '@/utils/logger';
/**
 * Frontend Code Block Enhancement
 * Adds copy buttons and line numbers to code blocks in rendered articles
 */

declare global {
    interface Window {
        enhanceCodeBlocks: () => void;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    enhanceCodeBlocks();
});

export function enhanceCodeBlocks() {
    const codeBlocks = document.querySelectorAll<HTMLPreElement>('.article-content pre');

    codeBlocks.forEach(function (pre) {
        // Skip if already enhanced
        if (pre.dataset.enhanced) return;
        
        // CRITICAL: Check if element is still connected to document
        // Prevents "insertBefore of null" errors in Vue SPAs when components unmount
        if (!pre.isConnected || !pre.parentNode) return;
        
        pre.dataset.enhanced = 'true';

        // Get code element
        const code = pre.querySelector('code');
        if (!code) return;

        // Add copy button
        const copyBtn = document.createElement('button');
        copyBtn.className = 'copy-btn';
        copyBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg><span>Copy</span>';
        copyBtn.title = 'Copy code';

        copyBtn.addEventListener('click', function () {
            const text = code.textContent || code.innerText;
            navigator.clipboard.writeText(text).then(function () {
                copyBtn.classList.add('copied');
                copyBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Copied!</span>';

                setTimeout(function () {
                    copyBtn.classList.remove('copied');
                    copyBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg><span>Copy</span>';
                }, 2000);
            }).catch(function (err) {
                logger.error('Failed to copy:', err);
            });
        });

        // Safe insertion with null check
        if (pre.isConnected && pre.parentNode) {
            pre.insertBefore(copyBtn, pre.firstChild);
        }

        // Add line numbers
        const content = code.textContent || '';
        const lines = content.split('\n');
        const wrappedLines = lines.map(function (line) {
            // Escape HTML in line
            const escapedLine = line
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            return '<span class="line">' + (escapedLine || ' ') + '</span>';
        });

        // Re-apply with wrapped lines (but preserve syntax highlighting if present)
        // Only add line numbers if not already present
        if (!code.querySelector('.line')) {
            // Simple check: if code has syntax highlighting classes, preserve them
            if (code.querySelector('.hljs-keyword') || code.querySelector('.hljs-string')) {
                // Has syntax highlighting, wrap each line but preserve highlighting
                const htmlLines = code.innerHTML.split('\n');
                const wrappedHtml = htmlLines.map(function (line) {
                    return '<span class="line">' + (line || ' ') + '</span>';
                }).join('\n');
                code.innerHTML = wrappedHtml;
            } else {
                // No syntax highlighting, use escaped version
                code.innerHTML = wrappedLines.join('\n');
            }
        }
    });
}

// Export for use in SPAs
if (typeof window !== 'undefined') {
    window.enhanceCodeBlocks = enhanceCodeBlocks;
}
