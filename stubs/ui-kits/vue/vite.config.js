import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  publicDir: false,
  cacheDir: 'storage/framework/vite',
  build: {
    outDir: 'public/build',
    emptyOutDir: false,
    rollupOptions: { input: { app: 'resources/js/app.js' } },
  },
});
