// @ts-check
import process from 'node:process';
import sitemap from '@astrojs/sitemap';
import solidJs from '@astrojs/solid-js';
import { defineConfig } from 'astro/config';

const port = Number(import.meta.env.PORT ?? '3000');
const site = process.env.SITE_URL ?? 'https://local.isekaijoucho.fan';

// https://astro.build/config
export default defineConfig({
  site: site,
  server: {
    host: true,
    port: port,
    allowedHosts: ['local.isekaijoucho.fan'],
  },
  integrations: [
    solidJs(),
    sitemap({
      // fragments はドロワー用の部分 HTML なのでクロール対象から外す
      filter: page => !page.includes('/fragments/'),
    }),
  ],
});
