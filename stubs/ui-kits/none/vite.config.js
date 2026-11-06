import { defineConfig } from 'vite';

export default defineConfig({
  publicDir: false,
  cacheDir: 'storage/framework/vite',
  build: {
    outDir: 'public/build',
    emptyOutDir: false,
    rollupOptions: { input: { app: 'resources/js/app.js' } },
  },
});
