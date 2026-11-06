import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  publicDir: false,
  cacheDir: 'storage/framework/vite',
  build: {
    outDir: 'public/build',
    emptyOutDir: false,
    rollupOptions: { input: { app: 'resources/js/app.js' } },
  },
});
