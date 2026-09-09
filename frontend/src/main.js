import { createPinia } from 'pinia'
import { createApp } from 'vue'

import App from './App.vue'
import { configureUnauthorizedHandler } from './api/http.js'
import { createAppRouter } from './router/index.js'
import { useAuthStore } from './stores/auth.js'
import './style.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)

const auth = useAuthStore(pinia)
const router = createAppRouter(auth)

configureUnauthorizedHandler(async () => {
  try {
    return await auth.refreshAccessToken()
  } catch (error) {
    const current = router.currentRoute.value
    if (current.name !== 'connexion') {
      await router.replace({
        name: 'connexion',
        query: { redirect: current.fullPath },
      })
    }
    throw error
  }
})

app.use(router)
app.mount('#app')
