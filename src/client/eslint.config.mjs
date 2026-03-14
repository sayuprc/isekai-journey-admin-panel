import eslint from '@eslint/js';
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
      'import': importPlugin,
    },
  },
  eslint.configs.recommended,
  ...tsEslint.configs.recommended,
  ...eslintPluginAstro.configs.recommended,
  {
    ignores: [
      '.astro/**',
      'dist/**',
      'src/generated/**',
    ],
  },
  {
    files: ['**/*.{js,mjs,ts,jsx,tsx,astro}'],
    rules: defaultRules,
  },
];
