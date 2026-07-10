import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    includeSource: ['src/**/*.{js,ts,tsx}'],
    // 既存の bun:test 分離ファイルは当面 bun で実行する
    exclude: [
      '**/node_modules/**',
      '**/dist/**',
      '**/.astro/**',
      '**/src/generated/**',
      '**/*.test.ts',
      '**/*.test.tsx',
    ],
  },
});
