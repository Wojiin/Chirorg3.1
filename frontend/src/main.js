import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { preloadViewForPath } from './router/viewLoaders'
import { configureApiAuth } from './api/axios'
import { createSessionExpiredHandler } from './services/sessionExpiry'
import { useAuthStore } from './stores/auth'
import { pinia } from './stores/pinia'
import { initializeTheme } from './composables/useTheme'
import './index.css'

/** Point d'entrée : restaure la session Pinia avant d'autoriser la première navigation. */
const app = createApp(App)
initializeTheme()

// Télécharge la vue demandée pendant la restauration de session, sans retarder le bootstrap.
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
