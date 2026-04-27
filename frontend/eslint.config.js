import js from '@eslint/js';
import vue from 'eslint-plugin-vue';
import tseslint from 'typescript-eslint';
import globals from 'globals';

export default tseslint.config(
    js.configs.recommended,
    ...tseslint.configs.recommended,
    ...vue.configs['flat/recommended'],
    {
        files: ['*.vue', '**/*.vue'],
        languageOptions: {
            parserOptions: {
                parser: '@typescript-eslint/parser',
            },
        },
    },
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
                ...globals.builtin,
                ...globals.es2021,
            },
        },
    },
    {
        rules: {
            'vue/multi-word-component-names': 'off',
            '@typescript-eslint/no-explicit-any': 'off',
            '@typescript-eslint/no-unused-vars': 'off',
            '@typescript-eslint/ban-ts-comment': 'off',
            'vue/no-v-text-v-html-on-component': 'off',
            'vue/max-attributes-per-line': 'off',
            'vue/html-indent': 'off',
            'vue/singleline-html-element-content-newline': 'off',
            'vue/html-self-closing': 'off',
            'vue/attributes-order': 'off',
            'no-unused-vars': 'off',
        },
    },
    {
        files: ['src/**/*.{ts,vue,js}'],
        ignores: [
            'src/modules/Cms/views/themes/janari/**',
            'src/composables/useGsapAnimations.ts',
            'src/lib/gsap.ts',
        ],
        rules: {
            'no-restricted-imports': ['error', {
                paths: [
                    {
                        name: 'gsap',
                        message: 'GSAP only allowed inside Janari theme.',
                    },
                    {
                        name: 'gsap/ScrollTrigger',
                        message: 'GSAP plugins only allowed inside Janari theme.',
                    },
                    {
                        name: 'gsap/Flip',
                        message: 'GSAP plugins only allowed inside Janari theme.',
                    },
                    {
                        name: 'gsap/Observer',
                        message: 'GSAP plugins only allowed inside Janari theme.',
                    },
                    {
                        name: '@/lib/gsap',
                        message: 'Use GSAP only from Janari theme files.',
                    },
                    {
                        name: '@/composables/useGsapAnimations',
                        message: 'useGsapAnimations is restricted to Janari theme.',
                    },
                ],
            }],
        },
    }
);
