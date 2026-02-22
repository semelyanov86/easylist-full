import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
import prettier from 'eslint-config-prettier';
import fsdImports from 'eslint-plugin-feature-sliced-design-imports';
import importPlugin from 'eslint-plugin-import';
import simpleImportSort from 'eslint-plugin-simple-import-sort';
import vue from 'eslint-plugin-vue';


const isCI = process.env.CI === 'true';
const isProd = process.env.NODE_ENV === 'production';

export default defineConfigWithVueTs(
    vue.configs['flat/essential'],
    vueTsConfigs.recommended,
    {
        ignores: [
            'vendor',
            'node_modules',
            'public',
            'bootstrap/ssr',
            'tailwind.config.js',
            'vite.config.ts',
            'resources/js/shared/ui/*',
            'resources/js/actions',
            'resources/js/routes',
        ],
    },
    {
        plugins: {
            import: importPlugin,
            'simple-import-sort': simpleImportSort,
            'feature-sliced-design-imports': fsdImports,
        },
        settings: {
            'import/resolver': {
                typescript: {
                    alwaysTryTypes: true,
                    project: './tsconfig.json',
                },
            },
            'feature-sliced-design-imports/alias': '@',
            'feature-sliced-design-imports/layers': {
                app: 'app',
                pages: 'pages',
                widgets: 'widgets',
                features: 'features',
                entities: 'entities',
                shared: 'shared',
            },
            'feature-sliced-design-imports/ignoreImports': [
                '**/*.css',
                '**/*.scss',
                '**/*.sass',
                '**/*.less',
                '**/*.svg',
            ],
            'feature-sliced-design-imports/ignoreFiles': [
                '**/*.d.ts',
                '**/*.config.*',
                '**/vite.config.*',
                '**/tailwind.config.*',
            ],
        },
        rules: {
            'no-debugger': isProd ? 'error' : 'warn',
            'no-console': isProd ? 'error' : 'warn',
            'no-alert': 'error',
            'no-var': 'error',
            'prefer-const': 'error',
            eqeqeq: ['error', 'always', { null: 'ignore' }],
            curly: ['error', 'all'],
            'no-duplicate-imports': 'error',

            'simple-import-sort/imports': 'error',
            'simple-import-sort/exports': 'off',

            'vue/multi-word-component-names': 'off',
            'vue/block-lang': [
                'error',
                {
                    script: { lang: 'ts' },
                },
            ],
            'vue/no-v-html': 'off',
            'vue/no-unused-properties': isCI ? 'error' : 'warn',
            'vue/no-ref-object-reactivity-loss': 'error',

            '@typescript-eslint/no-explicit-any': 'error',
            '@typescript-eslint/consistent-type-imports': [
                'error',
                {
                    prefer: 'type-imports',
                    fixStyle: 'separate-type-imports',
                },
            ],
            '@typescript-eslint/no-non-null-assertion': 'error',
            'import/order': 'off',
            'feature-sliced-design-imports/layer-imports': 'error',
            'feature-sliced-design-imports/relative-imports': [
                'error',
                {
                    ignoreFilesPattern: ['**/index.*'],
                },
            ],
            'feature-sliced-design-imports/public-api-imports': [
                'error',
                {
                    testFilePatterns: [
                        '**/*.test.*',
                        '**/*.spec.*',
                        '**/tests/**',
                    ],
                    ignoreFilesPattern: ['**/*.stories.*'],
                },
            ],
        },
    },
    prettier,
);
