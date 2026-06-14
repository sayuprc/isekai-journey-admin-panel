// @ts-check
import node from '@astrojs/node';
import solidJs from '@astrojs/solid-js';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig, envField } from 'astro/config';

const port = Number(import.meta.env.PORT ?? '4321');

// https://astro.build/config
export default defineConfig({
  output: 'server',
  server: {
    host: true,
    port: port,
    allowedHosts: ['local.admin.isekaijoucho.fan'],
  },
  adapter: node({
    mode: 'standalone',
  }),
  security: {
    checkOrigin: false,
  },
  env: {
    schema: {
      API_URL: envField.string({ context: 'server', access: 'secret' }),
      CACHE_URL: envField.string({ context: 'server', access: 'secret' }),
      CACHE_TOKEN: envField.string({ context: 'server', access: 'secret' }),
      PUBLIC_APP_URL: envField.string({ context: 'client', access: 'public' }),
    },
  },
  vite: {
    plugins: [tailwindcss()],
    ssr: {
      noExternal: true,
    },
  },
  integrations: [solidJs()],
});
