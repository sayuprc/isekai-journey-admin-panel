import eslint from '@eslint/js';
import stylistic from '@stylistic/eslint-plugin';
import eslintPluginAstro from 'eslint-plugin-astro';
import importPlugin from 'eslint-plugin-import';
import tsEslint from 'typescript-eslint';

const defaultRules = {
  'import/order': [
    'error',
    {
      alphabetize: {
        order: 'asc',
        caseInsensitive: true,
      },
    },
  ],
  '@stylistic/jsx-one-expression-per-line': [
    'error',
    {
      allow: 'single-line',
    },
  ],
  '@stylistic/brace-style': ['error', '1tbs'],
  '@typescript-eslint/consistent-type-imports': [
    'error',
    {
      fixStyle: 'separate-type-imports',
    },
  ],
};

export default [
  {
    plugins: {
      '@stylistic': stylistic,
      import: importPlugin,
    },
  },
  eslint.configs.recommended,
  ...tsEslint.configs.recommended,
  ...eslintPluginAstro.configs.recommended,
  stylistic.configs.customize({
    quotes: 'single',
    semi: true,
  }),
  {
    ignores: ['.astro/**', 'dist/**', 'src/generated/**'],
  },
  {
    files: ['**/*.{js,mjs,ts,jsx,tsx,astro}'],
    rules: defaultRules,
  },
  {
    files: ['**/*.astro'],
    rules: {
      '@stylistic/jsx-one-expression-per-line': 'off',
    },
  },
];
