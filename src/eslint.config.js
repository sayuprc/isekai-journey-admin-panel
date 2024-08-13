import stylistic from '@stylistic/eslint-plugin'
import js from '@eslint/js'

export default [
  {
    files: ['**/*.js'],
  },
  {
    ignores: [
      'node_modules/**',
      'vendor/**',
    ],
  },
  js.configs.recommended,
  stylistic.configs.customize({
    indent: 2,
    quotes: 'single',
    semi: false,
    jsx: false,
  }),
]
