import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    includeSource: ['src/**/*.{js,ts,tsx}'],
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
