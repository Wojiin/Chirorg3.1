import { adminResources } from '@/config/adminResources'

/** Expose le catalogue déclaratif des ressources administrables. */
export function useAdminDashboardView() {
  return { resources: adminResources }
}
