import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/ts/app.ts',
        'resources/ts/journey-log.ts',
      ],
      refresh: true,
    }),
  ],
  server: {
    host: '0.0.0.0',
    hmr: {
      host: 'localhost',
    },
  },
})
