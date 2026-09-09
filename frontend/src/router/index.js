import { createRouter, createWebHistory } from 'vue-router'

import { createNavigationGuard } from './guard.js'
import AccessDeniedView from '../views/AccessDeniedView.vue'
import AdminFormView from '../views/AdminFormView.vue'
import AdminListView from '../views/AdminListView.vue'
import AdministrationView from '../views/AdministrationView.vue'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'

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
