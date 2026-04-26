// @ts-check
import solidJs from '@astrojs/solid-js';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'astro/config';

const port = Number(import.meta.env.PORT ?? '4321');

// https://astro.build/config
export default defineConfig({
  output: 'server',
  server: {
    host: true,
    port: port,
    allowedHosts: ['local.admin.isekaijoucho.fan'],
  },
  security: {
    checkOrigin: false,
  },
  vite: {
    plugins: [tailwindcss()],
  },
  integrations: [solidJs()],
});
