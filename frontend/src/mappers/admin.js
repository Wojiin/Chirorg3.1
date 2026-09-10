/** Transforme une relation API en identifiant sélectionnable par le formulaire. */
function normalizeInitialValue(value) {
  if (value && typeof value === 'object') return value.id ?? ''
  return value ?? ''
}

/** Crée l'état éditable d'un formulaire depuis son schéma et une éventuelle ressource existante. */
export function createAdminForm(fields, existing = null) {
  return Object.fromEntries(
    fields.map((field) => {
      if (field.key === 'role') {
        const role = existing?.roles?.includes('ROLE_ADMIN')
          ? 'ROLE_ADMIN'
          : 'ROLE_USER'
        return [field.key, role]
      }

      if (field.type === 'material-picker') {
        return [
          field.key,
          (existing?.[field.key] ?? []).map(normalizeInitialValue),
        ]
      }

      return [field.key, normalizeInitialValue(existing?.[field.key])]
    }),
  )
}

/** Convertit les valeurs du formulaire en payload API Platform. */
export function buildAdminPayload(form) {
  const payload = { ...form }
  delete payload.imageFile
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
  if (payload.password) {
    payload.motDePasse = payload.password
  }
  delete payload.password
  if (Object.hasOwn(payload, 'description')) {
    payload.description = payload.description?.trim() || null
  }
  if (Object.hasOwn(payload, 'materiels')) {
    payload.materiels = payload.materiels.map((material) => {
      const id = typeof material === 'object' ? material.id : material
      return `/api/materiels/${id}`
    })
  }
  if (payload.ordre !== undefined && payload.ordre !== '') {
    payload.ordre = Number(payload.ordre)
  }

  return payload
}
