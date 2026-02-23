// @ts-check
import solidJs from '@astrojs/solid-js';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'astro/config';

// https://astro.build/config
export default defineConfig({
  output: 'server',
  server: {
    allowedHosts: ['local.admin.terrarium.isekaijoucho.fan'],
  },
  vite: {
    plugins: [tailwindcss()],
  },
  integrations: [solidJs()],
});
