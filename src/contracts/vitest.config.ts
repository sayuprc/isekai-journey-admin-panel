import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    includeSource: ['scripts/**/*.{js,ts}'],
    exclude: [
      '**/node_modules/**',
      '**/generated/**',
      '**/*.test.ts',
    ],
  },
});
