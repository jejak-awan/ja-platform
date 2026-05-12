import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import loggerPlugin, { logger } from '@/shared/utils/logger';
import api from '@/core/api/client';

vi.mock('@/core/api/client', () => ({
    default: {
        post: vi.fn().mockResolvedValue({}),
    }
}));

describe('logger util', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        vi.useFakeTimers();
        (logger as any).logCount = 0;
        (logger as any).lastResetTime = Date.now();
        (logger as any).signatureMap.clear();

        vi.spyOn(console, 'error').mockImplementation(() => { });
        vi.spyOn(console, 'warn').mockImplementation(() => { });
        vi.spyOn(console, 'log').mockImplementation(() => { });
        vi.spyOn(console, 'debug').mockImplementation(() => { });
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.restoreAllMocks();
    });

    it('logs info', () => {
        logger.info('Test info');
        expect(api.post).not.toHaveBeenCalled();
    });

    it('logs debug', () => {
        logger.debug('Test debug');
        expect(api.post).not.toHaveBeenCalled();
    });

    it('logs warning', () => {
        logger.warning('Test warning');
        expect(api.post).not.toHaveBeenCalled();
    });

    it('logs error and sends to backend', async () => {
        const errorData = { detail: 'error msg', stack: 'stacktrace' };
        logger.error('Test error', errorData);
        expect(api.post).toHaveBeenCalledTimes(1);
        const calls = vi.mocked(api.post).mock.calls;
        const logPayload = calls[0] ? (calls[0][1] as any) : {};
        expect(logPayload.message).toBe('Test error');
        expect(logPayload.level).toBe('error');
    });

    it('includes user_id from localStorage', async () => {
        localStorage.setItem('user', JSON.stringify({ id: 99 }));
        logger.error('Log with user');
        const calls = vi.mocked(api.post).mock.calls;
        const logPayload = calls[0] ? (calls[0][1] as any) : {};
        expect(logPayload.user_id).toBe(99);
    });

    it('truncates very long stack traces', async () => {
        const longStack = 'a'.repeat(1500);
        logger.error('Long stack', { stack: longStack });
        const calls = vi.mocked(api.post).mock.calls;
        const logPayload = calls[0] ? (calls[0][1] as any) : {};
        expect(logPayload.stack.length).toBeLessThan(1100);
        expect(logPayload.stack).toContain('(truncated for safety)');
    });

    it('handles global window handlers', () => {
        if (window.onerror) {
            window.onerror('Msg', 'source', 1, 1, new Error('stk'));
        }
        expect(api.post).toHaveBeenCalled();

        if (window.onunhandledrejection) {
            window.onunhandledrejection({ reason: new Error('reject msg') } as PromiseRejectionEvent);
        }
        expect(api.post).toHaveBeenCalledTimes(2);
    });

    it('deep truncates large objects', () => {
        const largeObject = { a: 'a'.repeat(300), b: { c: 'c'.repeat(300) }, d: ['d'.repeat(300)] };
        logger.error('Test truncation', largeObject);
        const calls = vi.mocked(api.post).mock.calls;
        const payload = calls[0] ? (calls[0][1] as any) : { data: {} };
        expect(payload.data.a).toContain('... (truncated)');
        expect(payload.data.b.c).toContain('... (truncated)');
        expect(payload.data.d[0]).toContain('... (truncated)');
    });

    it('stops recursing past depth 3', () => {
        const deeplyNested = { d1: { d2: { d3: { d4: { d5: 'too deep' } } } } };
        logger.error('Test depth', deeplyNested);
        const calls = vi.mocked(api.post).mock.calls;
        const payload = calls[0] ? (calls[0][1] as any) : { data: {} };
        expect(payload.data.d1.d2.d3.d4).toBe('[Max Depth Exceeded]');
    });

    it('applies rate limiting', () => {
        for (let i = 0; i < 25; i++) {
            logger.error(`Error ${i}`, { id: i });
        }
        expect(api.post).toHaveBeenCalledTimes(20);
        expect(console.warn).toHaveBeenCalledWith('[Logger] Rate limit exceeded. Further logs paused for 1 minute.');

        vi.advanceTimersByTime(61000);
        logger.error('Error 26', { id: 26 });
        expect(api.post).toHaveBeenCalledTimes(21);
    });

    it('handles Vue plugin installation', () => {
        const app = {
            config: { errorHandler: null, globalProperties: {} }
        };
        (app.config as any).installPlugin = true; // dummy
        loggerPlugin.install(app as any);
        expect(app.config.errorHandler).toBeTypeOf('function');
        expect((app.config.globalProperties as any).$logger).toBe(logger);

        // trigger error handler
        if (app.config.errorHandler) {
            (app.config.errorHandler as any)(new Error('Vue err'), { $options: { __file: 'Test.vue' } } as any, 'info');
        }
        expect(api.post).toHaveBeenCalled();
    });

    it('handles deduplication signature', () => {
        logger.error('Same error');
        logger.error('Same error');
        expect(api.post).toHaveBeenCalledTimes(1);

        vi.advanceTimersByTime(31000);
        logger.error('Same error');
        expect(api.post).toHaveBeenCalledTimes(2);
    });

    it('catches api send failures gracefully', async () => {
        vi.mocked(api.post).mockRejectedValueOnce(new Error('Network Error'));
        logger.error('Boom');
        await vi.runAllTimersAsync();
        expect(console.error).toHaveBeenCalledWith('Failed to send log to backend', expect.any(Error));
    });
});
