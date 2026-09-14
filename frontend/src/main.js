import { createApp } from 'vue'
import App from './App.vue'
import router, { preloadViewForPath } from './router'
import { configureApiAuth } from './api/axios'
import { createSessionExpiredHandler } from './services/sessionExpiry'
import { useAuthStore } from './stores/auth'
import { pinia } from './stores/pinia'
import { initializeTheme } from './composables/useTheme'
import './index.css'

/** Point d'entrée : restaure la session Pinia avant d'autoriser la première navigation. */
const app = createApp(App)
initializeTheme()

void preloadViewForPath(window.location.pathname)

app.use(pinia)

const authStore = useAuthStore(pinia)
const onSessionExpired = createSessionExpiredHandler({
  router,
  clearSession: () => authStore.clearSession(),
})
configureApiAuth({
  getAccessToken: () => authStore.token,
  setAccessToken: (token) => authStore.setAccessToken(token),
  onSessionExpired,
})

async function bootstrap() {
  if (window.location.pathname === '/login') {
    authStore.initializeGuestSession()
  } else {
    await authStore.initialize()
  }
  app.use(router)
  await router.isReady()
  app.mount('#app')
}

bootstrap()
