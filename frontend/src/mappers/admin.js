function relationId(value) {
  if (value && typeof value === 'object') return value.id ?? ''
  if (typeof value === 'string' && value.includes('/'))
    return value.split('/').pop()
  return value ?? ''
}

export function createAdminForm(fields, existing = null) {
  return Object.fromEntries(
    fields.map((field) => {
      if (field.key === 'role') {
        return [
          field.key,
          existing?.roles?.includes('ROLE_ADMIN') ? 'ROLE_ADMIN' : 'ROLE_USER',
        ]
      }
      if (field.key === 'actif') return [field.key, existing?.actif ?? true]
      if (field.type === 'multiselect') {
        return [field.key, (existing?.[field.key] ?? []).map(relationId)]
      }
      return [field.key, relationId(existing?.[field.key])]
    }),
  )
}

export function buildAdminPayload(form) {
  const payload = { ...form }
  const relations = {
    specialite: 'specialites',
    chirurgien: 'chirurgiens',
    chirurgieModele: 'chirurgie-modeles',
  }

  for (const [field, resource] of Object.entries(relations)) {
    if (payload[field]) payload[field] = `/api/${resource}/${payload[field]}`
  }
  if (payload.role) {
    payload.roles = [payload.role]
    delete payload.role
  }
  if (!payload.motDePasse) delete payload.motDePasse
  if (Object.hasOwn(payload, 'materiels')) {
    payload.materiels = payload.materiels.map(
      (id) => `/api/materiels/${relationId(id)}`,
    )
  }
  if (payload.ordre !== undefined && payload.ordre !== '')
    payload.ordre = Number(payload.ordre)
  if (Object.hasOwn(payload, 'description'))
    payload.description = payload.description?.trim() || null

  return payload
}
