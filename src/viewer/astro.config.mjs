// @ts-check
import solidJs from '@astrojs/solid-js';
import { defineConfig } from 'astro/config';

const port = Number(import.meta.env.PORT ?? '3000');

// https://astro.build/config
export default defineConfig({
  server: {
    host: true,
    port: port,
    allowedHosts: ['local.isekaijoucho.fan'],
  },
  vite: {
    define: {
      // in-source テストブロックを本番ビルドから除去する
      'import.meta.vitest': 'undefined',
    },
  },
  integrations: [solidJs()],
});
