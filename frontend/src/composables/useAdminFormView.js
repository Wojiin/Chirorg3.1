import { computed, reactive, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { getAdminResource } from '@/config/adminResources'
import { getAdminFormFields, referencesByResource } from '@/config/adminForms'
import { getMaterialsForSurgeon } from '@/domain/adminFilters'
import { buildAdminPayload, createAdminForm } from '@/mappers/admin'
import { useAdminStore } from '@/stores/admin'
import { useReferenceStore } from '@/stores/references'
import { technicalSheetApi } from '@/services/technicalSheetApi'
import { resolveApiAssetUrl } from '@/api/config'
import { getApiErrorMessage } from '@/api/response'

const acceptedImageTypes = ['image/jpeg', 'image/png', 'image/webp']
const maximumImageSize = 5 * 1024 * 1024

/** Orchestre le formulaire CRUD, ses référentiels et le téléversement éventuel. */
export function useAdminFormView(props) {
  let formLoadId = 0
  const router = useRouter()
  const adminStore = useAdminStore()
  const referenceStore = useReferenceStore()
  const {
    loading: itemLoading,
    saving,
    error: adminError,
  } = storeToRefs(adminStore)
  const { loading: referencesLoading, error: referenceError } =
    storeToRefs(referenceStore)
  const form = reactive({})
  const formError = ref('')
  const uploading = ref(false)
  const confirmationOpen = ref(false)
  const fileInputKey = ref(0)
  const resource = computed(() => getAdminResource(props.resourceSlug))
  const isEditing = computed(() => Number.isInteger(props.id) && props.id > 0)
  const eligibleMaterials = computed(() => {
    const materials = referenceStore.collections.materiels ?? []
    if (props.resourceSlug !== 'listes-materiel') return materials
    return getMaterialsForSurgeon(
      materials,
      referenceStore.collections.chirurgiens ?? [],
      form.chirurgien,
    )
  })
  const formCollections = computed(() => ({
    ...referenceStore.collections,
    materiels: eligibleMaterials.value,
  }))
  const fields = computed(() =>
    getAdminFormFields(props.resourceSlug, formCollections.value),
  )
  const loading = computed(
    () =>
      itemLoading.value ||
      referencesLoading.value ||
      saving.value ||
      uploading.value,
  )
  const displayedError = computed(() => {
    if (!resource.value) return 'Ce référentiel n’existe pas.'
    return formError.value || adminError.value || referenceError.value
  })
  const currentImageUrl = computed(() => resolveApiAssetUrl(form.lienImage))

  function replaceForm(values) {
    for (const key of Object.keys(form)) delete form[key]
    Object.assign(form, values)
  }

  async function loadForm() {
    const requestId = ++formLoadId
    formError.value = ''
    if (!resource.value) {
      replaceForm({})
      return
    }

    const references = referencesByResource[props.resourceSlug] ?? []
    try {
      const [, existing] = await Promise.all([
        referenceStore.load(references),
        isEditing.value
          ? adminStore.loadItem(props.resourceSlug, props.id)
          : Promise.resolve(null),
      ])
      if (requestId !== formLoadId) return
      const values = createAdminForm(fields.value, existing)
      if (props.resourceSlug === 'fiches-techniques') {
        values.lienImage = existing?.lienImage ?? null
      }
      replaceForm(values)
    } catch {
      if (requestId !== formLoadId) return
      const values = createAdminForm(fields.value)
      if (props.resourceSlug === 'fiches-techniques') values.lienImage = null
      replaceForm(values)
    }
  }

  function selectImage(event) {
    const image = event.target.files?.[0] ?? null
    formError.value = ''
    if (image && !acceptedImageTypes.includes(image.type)) {
      formError.value = 'Seules les images JPEG, PNG et WebP sont acceptées.'
      event.target.value = ''
      form.imageFile = null
      return
    }
    if (image && image.size > maximumImageSize) {
      formError.value = 'L’image ne peut pas dépasser 5 Mo.'
      event.target.value = ''
      form.imageFile = null
      return
    }
    form.imageFile = image
  }

  function removeImage() {
    form.imageFile = null
    form.lienImage = null
    fileInputKey.value += 1
  }

  function validateForm() {
    const missingField = fields.value.find(
      (field) =>
        field.required &&
        (Array.isArray(form[field.key])
          ? !form[field.key].length
          : !form[field.key]),
    )
    if (missingField)
      return `Le champ « ${missingField.label} » est obligatoire.`
    if (
      props.resourceSlug === 'fiches-techniques' &&
      !form.description?.trim() &&
      !form.imageFile &&
      !form.lienImage
    ) {
      return 'Ajoutez une consigne écrite, une image ou les deux.'
    }
    return ''
  }

  async function uploadImage(payload) {
    if (props.resourceSlug !== 'fiches-techniques' || !form.imageFile)
      return payload

    uploading.value = true
    try {
      return {
        ...payload,
        lienImage: await technicalSheetApi.uploadImage(form.imageFile),
      }
    } catch (error) {
      formError.value = getApiErrorMessage(
        error,
        'L’image n’a pas pu être téléversée.',
      )
      return null
    } finally {
      uploading.value = false
    }
  }

  function submit() {
    formError.value = validateForm()
    if (formError.value) return
    confirmationOpen.value = true
  }

  function cancelConfirmation() {
    confirmationOpen.value = false
  }

  async function confirmSubmit() {
    confirmationOpen.value = false

    const payload = await uploadImage(buildAdminPayload(form))
    if (!payload) return
    const savedItem = await adminStore.saveItem(
      props.resourceSlug,
      isEditing.value ? props.id : null,
      payload,
    )

    if (savedItem) {
      referenceStore.invalidate(props.resourceSlug)
      await router.push({
        name: 'admin-list',
        params: { resource: props.resourceSlug },
      })
    }
  }

  function goBack() {
    return router.push({
      name: 'admin-list',
      params: { resource: props.resourceSlug },
    })
  }

  watch(() => [props.resourceSlug, props.id], loadForm, { immediate: true })

  watch(
    () => form.chirurgien,
    (surgeonId, previousSurgeonId) => {
      if (
        props.resourceSlug !== 'listes-materiel' ||
        previousSurgeonId == null ||
        String(surgeonId) === String(previousSurgeonId)
      )
        return

      const eligibleIds = new Set(
        eligibleMaterials.value.map((material) => String(material.id)),
      )
      form.materiels = (form.materiels ?? []).filter((id) =>
        eligibleIds.has(String(id)),
      )
    },
  )

  return {
    currentImageUrl,
    confirmationOpen,
    cancelConfirmation,
    confirmSubmit,
    displayedError,
    fields,
    fileInputKey,
    form,
    isEditing,
    loading,
    goBack,
    removeImage,
    resource,
    saving,
    selectImage,
    submit,
    uploading,
  }
}
