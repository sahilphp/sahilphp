import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [tailwindcss()],
  publicDir: false,
  cacheDir: 'storage/framework/vite',
  build: {
    outDir: 'public/build',
    emptyOutDir: false,
    rollupOptions: {
      input: { app: 'resources/js/app.js' },
      output: {
        entryFileNames: 'assets/[name].js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: 'assets/[name][extname]',
      },
    },
  },
});
