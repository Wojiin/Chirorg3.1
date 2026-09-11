import { createRouter, createWebHistory } from 'vue-router'
import AppShell from '@/components/ui/AppShell.vue'
import { ERROR_MESSAGES } from '@/config/errorMessages'
import { installAccessGuard } from '@/router/accessGuard'
import { viewLoaders } from '@/router/viewLoaders'

/** Décrit la SPA : connexion publique, shell protégé et vues chargées à la demande. */
const routes = [
  {
    path: '/login',
    name: 'login',
    component: viewLoaders.login,
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
        component: viewLoaders.programme,
        meta: { title: 'Programme opératoire' },
      },
      {
        path: 'programmes/:date/:salle/:chirurgien',
        name: 'programme-detail',
        component: viewLoaders.programmeDetail,
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
        component: viewLoaders.planification,
        meta: { title: 'Planifier un programme' },
      },
      {
        path: 'chirurgies/:id/preparation',
        name: 'preparation',
        component: viewLoaders.preparation,
        props: (route) => ({ id: Number(route.params.id) }),
        meta: { title: 'Préparation' },
      },
      {
        path: 'chirurgies/:id/validation-partielle',
        name: 'validation-partielle',
        component: viewLoaders.partialValidation,
        props: (route) => ({ id: Number(route.params.id) }),
        meta: { title: 'Validation partielle' },
      },
      {
        path: 'chirurgies/:id/vue-finale',
        name: 'vue-finale',
        component: viewLoaders.finalView,
        props: (route) => ({ id: Number(route.params.id) }),
        meta: { title: 'Vue finale' },
      },
      {
        path: 'admin',
        name: 'admin',
        component: viewLoaders.adminDashboard,
        meta: { requiresAdmin: true, title: 'Administration' },
      },
      {
        path: 'admin/:resource/new',
        name: 'admin-new',
        component: viewLoaders.adminForm,
        props: (route) => ({ resourceSlug: route.params.resource, id: null }),
        meta: { requiresAdmin: true, title: 'Ajouter une ressource' },
      },
      {
        path: 'admin/:resource/:id/edit',
        name: 'admin-edit',
        component: viewLoaders.adminForm,
        props: (route) => ({
          resourceSlug: route.params.resource,
          id: Number(route.params.id),
        }),
        meta: { requiresAdmin: true, title: 'Modifier une ressource' },
      },
      {
        path: 'admin/:resource',
        name: 'admin-list',
        component: viewLoaders.adminList,
        props: (route) => ({ resourceSlug: route.params.resource }),
        meta: { requiresAdmin: true, title: 'Référentiel' },
      },
      {
        path: 'compte',
        name: 'account',
        component: viewLoaders.account,
        meta: { title: 'Mon compte' },
      },
      {
        path: ':pathMatch(.*)*',
        name: 'not-found',
        component: viewLoaders.notFound,
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

export default router
