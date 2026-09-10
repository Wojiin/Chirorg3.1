import { reactive } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

/** Orchestre l'authentification et la redirection de la vue de connexion. */
export function useLoginView(props) {
  const router = useRouter()
  const authStore = useAuthStore()
  const { error, loading } = storeToRefs(authStore)
  const form = reactive({ email: '', password: '' })

  function destinationAfterLogin() {
    if (props.redirect.startsWith('/') && !props.redirect.startsWith('//')) {
      return props.redirect
    }
    return { name: 'programme' }
  }

  async function submit() {
    if (await authStore.login({ ...form })) {
      await router.replace(destinationAfterLogin())
    }
  }

  return { error, form, loading, submit }
}
