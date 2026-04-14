// @ts-check
import solidJs from '@astrojs/solid-js';
import { defineConfig } from 'astro/config';

// https://astro.build/config
export default defineConfig({
  server: {
    allowedHosts: ['local.terrarium.isekaijoucho.fan'],
  },
  integrations: [solidJs()],
});
