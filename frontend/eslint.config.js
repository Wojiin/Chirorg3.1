import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'

export default [
  {
    ignores: [
      'dist/**',
      'coverage/**',
      'playwright-report/**',
      'test-results/**',
    ],
  },
  js.configs.recommended,
  ...pluginVue.configs['flat/essential'],
  {
    languageOptions: {
      globals: {
        atob: 'readonly',
        btoa: 'readonly',
        document: 'readonly',
        File: 'readonly',
        FormData: 'readonly',
        KeyboardEvent: 'readonly',
        process: 'readonly',
        Storage: 'readonly',
        URL: 'readonly',
        window: 'readonly',
      },
    },
    rules: {
      'vue/multi-word-component-names': 'off',
      'vue/valid-template-root': 'off',
    },
  },
]
