import { fileURLToPath, URL } from 'node:url'

import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { defineConfig } from 'vite'

const sourceDirectory = fileURLToPath(new URL('./src', import.meta.url))
const piniaProductionBuild = fileURLToPath(
  new URL(
    './node_modules/pinia/dist/pinia.esm-browser.prod.js',
    import.meta.url,
  ),
)

/** Sélectionne le build Pinia minimal uniquement pour les livrables de production. */
export default defineConfig(({ command }) => ({
  plugins: [vue(), tailwindcss()],
  resolve: {
    alias: {
      '@': sourceDirectory,
      ...(command === 'build' ? { pinia: piniaProductionBuild } : {}),
    },
  },
  test: {
    environment: 'jsdom',
    include: ['tests/unit/**/*.test.js'],
    coverage: {
      provider: 'v8',
      include: ['src/**/*.{js,vue}'],
      exclude: ['src/main.js'],
      reporter: ['text', 'json-summary', 'lcov', 'html'],
      reportOnFailure: true,
      thresholds: {
        lines: 80,
        functions: 80,
        branches: 75,
        statements: 80,
      },
    },
  },
}))
