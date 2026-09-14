import { createRouter, createWebHistory } from 'vue-router'
import AppShell from '@/components/ui/AppShell.vue'
import { ERROR_MESSAGES } from '@/config/errorMessages'
import { applyPageMetadata } from '@/config/pageMetadata'
import { installAccessGuard } from '@/router/accessGuard'

/** Décrit la SPA : connexion publique, shell protégé et vues chargées à la demande. */
const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginView.vue'),
    props: (route) => ({
      redirect:
        typeof route.query.redirect === 'string' ? route.query.redirect : '',
    }),
    meta: { guestOnly: true, title: 'Connexion' },
  },
  {
    path: '/',
    component: AppShell,
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: { name: 'programme' } },
      {
        path: 'programme',
        name: 'programme',
        component: () => import('@/views/ProgrammeOperatoireView.vue'),
        meta: { title: 'Programme opératoire' },
      },
      {
        path: 'programmes/:date/:salle/:chirurgien',
        name: 'programme-detail',
        component: () => import('@/views/ProgrammeDetailView.vue'),
        props: (route) => ({
          date: route.params.date,
          salle: route.params.salle,
          chirurgienId: Number(route.params.chirurgien),
        }),
        meta: { title: 'Détail du programme' },
      },
      {
        path: 'planifier',
        name: 'planification',
        component: () => import('@/views/PlanificationView.vue'),
        meta: { title: 'Planifier un programme' },
      },
      {
        path: 'chirurgies/:id/preparation',
        name: 'preparation',
        component: () => import('@/views/PreparationView.vue'),
        props: (route) => ({ id: Number(route.params.id) }),
        meta: { title: 'Préparation' },
      },
      {
        path: 'chirurgies/:id/validation-partielle',
        name: 'validation-partielle',
        component: () => import('@/views/ValidationPartielleView.vue'),
        props: (route) => ({ id: Number(route.params.id) }),
        meta: { title: 'Validation partielle' },
      },
      {
        path: 'chirurgies/:id/vue-finale',
        name: 'vue-finale',
        component: () => import('@/views/VueFinaleView.vue'),
        props: (route) => ({ id: Number(route.params.id) }),
        meta: { title: 'Vue finale' },
      },
      {
        path: 'admin',
        name: 'admin',
        component: () => import('@/views/AdminDashboardView.vue'),
        meta: { requiresAdmin: true, title: 'Administration' },
      },
      {
        path: 'admin/:resource/new',
        name: 'admin-new',
        component: () => import('@/views/AdminFormView.vue'),
        props: (route) => ({ resourceSlug: route.params.resource, id: null }),
        meta: { requiresAdmin: true, title: 'Ajouter une ressource' },
      },
      {
        path: 'admin/:resource/:id/edit',
        name: 'admin-edit',
        component: () => import('@/views/AdminFormView.vue'),
        props: (route) => ({
          resourceSlug: route.params.resource,
          id: Number(route.params.id),
        }),
        meta: { requiresAdmin: true, title: 'Modifier une ressource' },
      },
      {
        path: 'admin/:resource',
        name: 'admin-list',
        component: () => import('@/views/AdminListView.vue'),
        props: (route) => ({ resourceSlug: route.params.resource }),
        meta: { requiresAdmin: true, title: 'Référentiel' },
      },
      {
        path: 'compte',
        name: 'account',
        component: () => import('@/views/AccountView.vue'),
        meta: { title: 'Mon compte' },
      },
      {
        path: ':pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/views/NotFoundView.vue'),
        meta: { title: ERROR_MESSAGES.notFoundPageTitle },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

installAccessGuard(router)
router.afterEach(applyPageMetadata)

/** Précharge le composant résolu par le routeur pendant la restauration de session. */
export async function preloadViewForPath(pathname) {
  const target = pathname === '/' ? { name: 'programme' } : pathname
  const component = router.resolve(target).matched.at(-1)?.components?.default

  if (typeof component === 'function') await component()
}

export default router
