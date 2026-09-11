/** Chargeurs partagés entre le routeur et le préchargement du premier écran. */
export const viewLoaders = {
  login: () => import('@/views/LoginView.vue'),
  programme: () => import('@/views/ProgrammeOperatoireView.vue'),
  programmeDetail: () => import('@/views/ProgrammeDetailView.vue'),
  planification: () => import('@/views/PlanificationView.vue'),
  preparation: () => import('@/views/PreparationView.vue'),
  partialValidation: () => import('@/views/ValidationPartielleView.vue'),
  finalView: () => import('@/views/VueFinaleView.vue'),
  adminDashboard: () => import('@/views/AdminDashboardView.vue'),
  adminForm: () => import('@/views/AdminFormView.vue'),
  adminList: () => import('@/views/AdminListView.vue'),
  account: () => import('@/views/AccountView.vue'),
  notFound: () => import('@/views/NotFoundView.vue'),
}

/** Résout le composant correspondant à une URL sans attendre la restauration JWT. */
export function getViewLoaderForPath(pathname) {
  const path = pathname.replace(/\/+$/, '') || '/'

  if (path === '/login') return viewLoaders.login
  if (path === '/' || path === '/programme') return viewLoaders.programme
  if (path.startsWith('/programmes/')) return viewLoaders.programmeDetail
  if (path === '/planifier') return viewLoaders.planification
  if (/^\/chirurgies\/[^/]+\/preparation$/.test(path)) {
    return viewLoaders.preparation
  }
  if (/^\/chirurgies\/[^/]+\/validation-partielle$/.test(path)) {
    return viewLoaders.partialValidation
  }
  if (/^\/chirurgies\/[^/]+\/vue-finale$/.test(path)) {
    return viewLoaders.finalView
  }
  if (path === '/admin') return viewLoaders.adminDashboard
  if (/^\/admin\/[^/]+\/(new|[^/]+\/edit)$/.test(path)) {
    return viewLoaders.adminForm
  }
  if (path.startsWith('/admin/')) return viewLoaders.adminList
  if (path === '/compte') return viewLoaders.account

  return viewLoaders.notFound
}

/** Démarre le téléchargement du premier écran en parallèle du refresh token. */
export function preloadViewForPath(pathname) {
  return getViewLoaderForPath(pathname)()
}
