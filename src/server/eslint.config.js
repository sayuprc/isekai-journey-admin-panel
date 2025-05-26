import stylistic from '@stylistic/eslint-plugin'
import js from '@eslint/js'
import ts from 'typescript-eslint'

export default [
  {
    files: ['**/*.{js,ts}'],
  },
  {
    ignores: [
      'node_modules/**',
      'vendor/**',
      'public/build/**',
      'public/vendor/**',
    ],
  },
  js.configs.recommended,
  ...ts.configs.recommended,
  stylistic.configs.customize({
    indent: 2,
    quotes: 'single',
    semi: false,
    jsx: false,
  }),
]
