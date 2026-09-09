import { createRouter, createWebHistory } from 'vue-router'

import { createNavigationGuard } from './guard.js'
import AccessDeniedView from '../views/AccessDeniedView.vue'
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
