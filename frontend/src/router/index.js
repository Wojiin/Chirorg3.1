import { createRouter, createWebHistory } from 'vue-router'

import { createNavigationGuard } from './guard.js'
import AccessDeniedView from '../views/AccessDeniedView.vue'
import AdminFormView from '../views/AdminFormView.vue'
import AdminListView from '../views/AdminListView.vue'
import AdministrationView from '../views/AdministrationView.vue'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import PlanificationView from '../views/PlanificationView.vue'
import PreparationView from '../views/PreparationView.vue'
import ProgrammeDetailView from '../views/ProgrammeDetailView.vue'
import ProgrammesView from '../views/ProgrammesView.vue'
import VueFinaleView from '../views/VueFinaleView.vue'

const routes = [
  {
    path: '/connexion',
    name: 'connexion',
    component: LoginView,
    meta: { guestOnly: true, title: 'Connexion' },
  },
  {
    path: '/',
    name: 'accueil',
    component: HomeView,
    meta: {
      requiresAuth: true,
      roles: ['ROLE_USER'],
      shell: true,
      title: 'Accueil',
    },
  },
  {
    path: '/programmes',
    name: 'programmes',
    component: ProgrammesView,
    meta: {
      requiresAuth: true,
      roles: ['ROLE_USER'],
      shell: true,
      title: 'Programmes opératoires',
    },
  },
  {
    path: '/programmes/planifier',
    name: 'planification',
    component: PlanificationView,
    meta: {
      requiresAuth: true,
      roles: ['ROLE_USER'],
      shell: true,
      title: 'Planifier un programme',
    },
  },
  {
    path: '/programmes/:date/:salle/:chirurgien',
    name: 'programme-detail',
    component: ProgrammeDetailView,
    props: (route) => ({
      date: route.params.date,
      salle: route.params.salle,
      chirurgien: Number(route.params.chirurgien),
    }),
    meta: {
      requiresAuth: true,
      roles: ['ROLE_USER'],
      shell: true,
      title: 'Détail du programme',
    },
  },
  {
    path: '/chirurgies/:id/preparation',
    name: 'preparation',
    component: PreparationView,
    props: (route) => ({ id: Number(route.params.id) }),
    meta: {
      requiresAuth: true,
      roles: ['ROLE_USER'],
      shell: true,
      title: 'Préparation du matériel',
    },
  },
  {
    path: '/chirurgies/:id/vue-finale',
    name: 'vue-finale',
    component: VueFinaleView,
    props: (route) => ({ id: Number(route.params.id) }),
    meta: {
      requiresAuth: true,
      roles: ['ROLE_USER'],
      shell: true,
      title: 'Vue finale',
    },
  },
  {
    path: '/administration',
    name: 'administration',
    component: AdministrationView,
    meta: {
      requiresAuth: true,
      roles: ['ROLE_ADMIN'],
      shell: true,
      title: 'Administration',
    },
  },
  {
    path: '/administration/:resource',
    name: 'admin-list',
    component: AdminListView,
    props: true,
    meta: {
      requiresAuth: true,
      roles: ['ROLE_ADMIN'],
      shell: true,
      title: 'Référentiel',
    },
  },
  {
    path: '/administration/:resource/ajouter',
    name: 'admin-create',
    component: AdminFormView,
    props: true,
    meta: {
      requiresAuth: true,
      roles: ['ROLE_ADMIN'],
      shell: true,
      title: 'Ajouter',
    },
  },
  {
    path: '/administration/:resource/:id/modifier',
    name: 'admin-edit',
    component: AdminFormView,
    props: (route) => ({
      resource: route.params.resource,
      id: Number(route.params.id),
    }),
    meta: {
      requiresAuth: true,
      roles: ['ROLE_ADMIN'],
      shell: true,
      title: 'Modifier',
    },
  },
  {
    path: '/acces-refuse',
    name: 'acces-refuse',
    component: AccessDeniedView,
    meta: { title: 'Accès refusé' },
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

export function createAppRouter(authStore, history = createWebHistory()) {
  const router = createRouter({ history, routes })
  router.beforeEach(createNavigationGuard(authStore))
  router.afterEach((to) => {
    document.title = `${to.meta.title || 'Application'} · ChirOrg`
  })

  return router
}
