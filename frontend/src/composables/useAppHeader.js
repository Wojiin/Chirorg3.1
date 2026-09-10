import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

/** Orchestre le titre et la déconnexion du shell authentifié. */
export function useAppHeader() {
  const route = useRoute()
  const router = useRouter()
  const authStore = useAuthStore()
  const { displayName, isAdmin } = storeToRefs(authStore)
  const title = computed(
    () => route.meta.resolvedTitle ?? route.meta.title ?? 'ChirOrg',
  )

  async function logout() {
    await authStore.logout()
    await router.replace({ name: 'login' })
  }

  return { displayName, isAdmin, logout, title }
}
