import { reactive } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Regroupe l'état réactif et les actions de la page consacrée à la session de l'utilisateur. Dépendances utilisées : authStore, reactive, router, storeToRefs, useAuthStore, useRouter.
/** Orchestre l'authentification et la redirection de la vue de connexion. */
export function useLoginView(props) {
  const router = useRouter()
  const authStore = useAuthStore()
  const { error, loading } = storeToRefs(authStore)
  const form = reactive({ email: '', password: '' })

  // Détermine la page à afficher après la connexion. Dépendances utilisées : aucune dépendance importée directe.
  function destinationAfterLogin() {
    if (props.redirect.startsWith('/') && !props.redirect.startsWith('//')) {
      return props.redirect
    }
    return { name: 'programme' }
  }

  // Valide puis envoie le formulaire consacré à la session de l'utilisateur. Dépendances utilisées : authStore, router.
  async function submit() {
    if (await authStore.login({ ...form })) {
      await router.replace(destinationAfterLogin())
    }
  }

  return { error, form, loading, submit }
}
