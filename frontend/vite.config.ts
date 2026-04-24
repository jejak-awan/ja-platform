import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { fileURLToPath, URL } from 'node:url'
import { visualizer } from 'rollup-plugin-visualizer'

// https://vite.dev/config/
export default defineConfig({
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
    rollupOptions: {
      output: {
        chunkFileNames: 'assets/[hash].js',
        entryFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash].[ext]',
        manualChunks: (id) => {
          if (!id.includes('node_modules')) {
            return;
          }

          if (id.includes('lucide-vue-next')) {
            return 'vendor-icons';
          }

          // Keep Vue runtime ecosystem together for stable long-term caching.
          if (
            id.includes('node_modules/vue/') ||
            id.includes('node_modules/vue-router/') ||
            id.includes('node_modules/pinia/') ||
            id.includes('node_modules/@vue/')
          ) {
            return 'vendor-vue';
          }

          // Split heavy rich-text stack from base UI/runtime bundles.
          if (id.includes('@tiptap') || id.includes('prosemirror')) {
            return 'vendor-tiptap';
          }

          if (id.includes('highlight.js')) {
            return 'vendor-highlightjs';
          }

          if (id.includes('lowlight')) {
            return 'vendor-lowlight';
          }

          if (id.includes('marked') || id.includes('turndown')) {
            return 'vendor-markdown';
          }

          // Keep component system dependencies in a dedicated UI chunk.
          if (id.includes('radix-vue') || id.includes('reka-ui') || id.includes('class-variance-authority') || id.includes('tailwind-merge')) {
            return 'vendor-ui';
          }

          if (id.includes('@vueuse/core') || id.includes('@unhead/vue')) {
            return 'vendor-vue-utils';
          }

          if (
            id.includes('axios') ||
            id.includes('zod') ||
            id.includes('dayjs') ||
            id.includes('dompurify') ||
            id.includes('lodash') ||
            id.includes('qrcode') ||
            id.includes('cropperjs') ||
            id.includes('lottie-web')
          ) {
            return 'vendor-utils';
          }

          if (id.includes('@fullcalendar')) {
            return 'vendor-ui-calendar';
          }

          if (id.includes('chart.js') || id.includes('vue-chartjs')) {
            return 'vendor-ui-charts';
          }

          if (id.includes('gsap')) {
            return 'vendor-gsap';
          }

          // Fallback vendor chunk for remaining third-party deps.
          return 'vendor-misc';
        },
      },
    },
    // Keep warnings meaningful: current largest vendor chunk (highlight stack)
    // is ~1.02MB after minification.
    chunkSizeWarningLimit: 1100,
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
