import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';

export default defineConfig({
  plugins: [svelte()],
  publicDir: false,
  cacheDir: 'storage/framework/vite',
  build: {
    outDir: 'public/build',
    emptyOutDir: false,
    rollupOptions: { input: { app: 'resources/js/app.js' } },
  },
});
