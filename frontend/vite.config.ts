import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { fileURLToPath, URL } from 'node:url'
import { visualizer } from 'rollup-plugin-visualizer'

// https://vite.dev/config/
export default defineConfig({
  envDir: '../backend',
  test: {
    environment: 'happy-dom',
    globals: true,
    exclude: ['node_modules', 'tests/e2e/**'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: ['src/lib/utils.ts', 'src/main.ts', 'src/App.vue', 'src/router/**', 'src/types/**', 'src/vite-env.d.ts'],
    },
  },
  plugins: [
    vue(),
    tailwindcss(),
    // SRI (Subresource Integrity) Recommendation:
    // To prevent supply-chain attacks, it is highly recommended to install and use 'vite-plugin-sri'.
    // Once installed, add it here: sri(),
    visualizer({
      filename: './dist/stats.html',
      open: false,
      gzipSize: true,
      brotliSize: true,
    }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  build: {
    // Use esbuild CSS minifier to keep Tailwind v4 directives in scoped blocks
    // from being reported as unknown by lightningcss during production builds.
    cssMinify: 'esbuild',
    rolldownOptions: {
      checks: {
        pluginTimings: false,
      },
    },
    rollupOptions: {
      output: {
        chunkFileNames: 'assets/[hash].js',
        entryFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash].[ext]',
        manualChunks: (id) => {
          // Janari theme pages: keep heavy CMS theme views out of the main entry chunk.
          if (id.includes('/modules/Cms/views/themes/janari/') && id.endsWith('.vue')) {
            return 'theme-janari'
          }

          if (!id.includes('node_modules')) {
            return;
          }

          // Individual heavy icons to avoid one massive icons chunk
          if (id.includes('lucide-vue-next')) {
            return 'vendor-icons';
          }

          // Core Vue & Router
          if (
            id.includes('node_modules/vue/') ||
            id.includes('node_modules/vue-router/') ||
            id.includes('node_modules/@vue/runtime')
          ) {
            return 'vendor-vue-core';
          }

          // State management
          if (id.includes('pinia')) {
            return 'vendor-pinia';
          }

          // Split heavy rich-text stack
          if (id.includes('@tiptap') || id.includes('prosemirror')) {
            return 'vendor-tiptap';
          }

          if (id.includes('highlight.js') || id.includes('lowlight')) {
            return 'vendor-syntax-highlighter';
          }

          // UI Frameworks
          if (id.includes('radix-vue') || id.includes('reka-ui')) {
            return 'vendor-ui-core';
          }

          // Form & Validation
          if (id.includes('zod') || id.includes('vee-validate')) {
            return 'vendor-forms';
          }

          // Utilities
          if (id.includes('axios') || id.includes('dayjs') || id.includes('lodash')) {
            return 'vendor-utils-base';
          }

          if (id.includes('gsap')) {
            return 'vendor-animation';
          }

          if (id.includes('@fullcalendar')) {
            return 'vendor-ui-calendar';
          }

          if (id.includes('chart.js') || id.includes('vue-chartjs')) {
            return 'vendor-ui-charts';
          }

          // Fallback vendor chunk
          return 'vendor-misc';
        },
      },
    },
    // Current largest generated chunk is around ~1.65MB; keep warning threshold
    // above that so build output only flags newly larger regressions.
    chunkSizeWarningLimit: 1800,
    sourcemap: false,
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false
      },
      '/sanctum': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false
      }
    }
  }
})
