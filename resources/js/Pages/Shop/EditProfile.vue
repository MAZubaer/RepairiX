<template>
  <AppLayout title="Shop profile">
    <PageHeader title="Shop profile" subtitle="This is what customers see when they browse and open your shop.">
      <template #actions>
        <Link href="/dashboard/shop" class="btn btn-secondary">
          <Eye class="size-4" />
          View overview
        </Link>
      </template>
    </PageHeader>

    <form class="space-y-6 pb-28" novalidate @submit.prevent="submit">
      <!-- Business details -->
      <section class="card grid gap-8 p-6 sm:p-8 lg:grid-cols-3">
        <div>
          <h2 class="font-bold text-slate-900">Business details</h2>
          <p class="mt-1 text-sm text-slate-500">How customers find and contact you.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:col-span-2">
          <FormField id="shop_name" label="Shop name" :icon="Store" :error="errors.shop_name">
            <template #default="{ id, inputClass }">
              <input :id="id" v-model="form.shop_name" type="text" required :class="inputClass" />
            </template>
          </FormField>
          <FormField id="email" label="Business email" :icon="Mail" :error="errors.email" hint="Your login email can't be changed.">
            <template #default="{ id, inputClass }">
              <input :id="id" v-model="form.email" type="email" readonly :class="inputClass" />
            </template>
          </FormField>
          <FormField id="phone" label="Phone number" :icon="Phone" :error="errors.phone">
            <template #default="{ id, inputClass }">
              <input :id="id" v-model="form.phone" type="tel" required :class="inputClass" />
            </template>
          </FormField>
          <FormField id="shop_address" label="Shop address" :icon="MapPin" :error="errors.shop_address">
            <template #default="{ id, inputClass }">
              <input :id="id" v-model="form.shop_address" type="text" required :class="inputClass" />
            </template>
          </FormField>
        </div>
      </section>

      <!-- About -->
      <section class="card grid gap-8 p-6 sm:p-8 lg:grid-cols-3">
        <div>
          <h2 class="font-bold text-slate-900">About your shop</h2>
          <p class="mt-1 text-sm text-slate-500">A short motto and the repairs you offer.</p>
        </div>
        <div class="space-y-5 lg:col-span-2">
          <FormField id="motto" label="Motto" optional :error="errors.motto">
            <template #default="{ id, inputClass }">
              <input :id="id" v-model="form.motto" type="text" maxlength="255" :class="inputClass" placeholder="e.g. Fast, honest phone repairs" />
            </template>
          </FormField>
          <FormField id="services_provided" label="Services" optional :error="errors.services_provided" hint="One service per line works well.">
            <template #default="{ id, inputClass }">
              <textarea :id="id" v-model="form.services_provided" rows="6" :class="inputClass" placeholder="Screen replacement&#10;Battery replacement&#10;Charging port repair" />
            </template>
          </FormField>
        </div>
      </section>

      <!-- Logo -->
      <section class="card grid gap-8 p-6 sm:p-8 lg:grid-cols-3">
        <div>
          <h2 class="font-bold text-slate-900">Logo</h2>
          <p class="mt-1 text-sm text-slate-500">Square images look best. PNG, JPG or WebP, up to 4 MB.</p>
        </div>
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center lg:col-span-2">
          <Avatar :name="form.shop_name" :src="logoPreview || logo?.url" size="xl" rounded class="shadow-soft" />
          <div class="space-y-3">
            <div class="flex flex-wrap gap-2">
              <label class="btn btn-secondary cursor-pointer">
                <Upload class="size-4" />
                {{ logo || logoPreview ? 'Replace logo' : 'Upload logo' }}
                <input type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" @change="onLogoChange" />
              </label>
              <button v-if="logoPreview" type="button" class="btn btn-ghost" @click="clearLogo">Undo</button>
              <button v-else-if="logo" type="button" class="btn btn-soft-danger" @click="confirmDelete = { id: logo.image_id, kind: 'logo' }">
                <Trash2 class="size-4" />
                Remove
              </button>
            </div>
            <p v-if="logoPreview" class="text-xs font-medium text-brand-700">New logo selected. Save changes to apply it.</p>
            <p v-if="errors.logo" class="field-error">{{ errors.logo }}</p>
          </div>
        </div>
      </section>

      <!-- Photos live on their own page -->
      <Link href="/shop/photos" class="card card-hover group flex items-center gap-5 p-6 sm:p-8">
        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-600">
          <Camera class="size-7" />
        </span>
        <div class="min-w-0 flex-1">
          <h2 class="font-bold text-slate-900">Photos</h2>
          <p class="mt-1 text-sm text-slate-500">
            {{ photoCount ? `${photoCount} photo${photoCount === 1 ? '' : 's'} on your shop page.` : 'No photos yet.' }}
            Upload, reorder and choose your cover photo.
          </p>
        </div>
        <span class="btn btn-secondary shrink-0">
          Manage photos
          <ArrowRight class="size-4 transition group-hover:translate-x-0.5" />
        </span>
      </Link>

      <!-- Sticky save bar -->
      <div class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white/90 backdrop-blur">
        <div class="container-page flex items-center justify-between gap-3 py-3">
          <p class="hidden text-sm text-slate-500 sm:block">
            <span v-if="form.isDirty" class="font-medium text-amber-700">You have unsaved changes.</span>
            <span v-else>All changes saved.</span>
          </p>
          <div class="flex w-full gap-2 sm:w-auto">
            <button type="button" class="btn btn-ghost flex-1 sm:flex-none" :disabled="!form.isDirty || form.processing" @click="resetForm">Discard</button>
            <button type="submit" class="btn btn-primary flex-1 sm:flex-none" :disabled="form.processing">
              <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
              Save changes
            </button>
          </div>
        </div>
      </div>
    </form>

    <ConfirmDialog
      :show="!!confirmDelete"
      danger
      title="Remove logo?"
      message="This can't be undone."
      confirm-label="Delete"
      :processing="deleting"
      @cancel="confirmDelete = null"
      @confirm="deleteImage"
    />
  </AppLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { ArrowRight, Camera, Eye, LoaderCircle, Mail, MapPin, Phone, Store, Trash2, Upload } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import Avatar from '../../Components/Avatar.vue'
import ConfirmDialog from '../../Components/ConfirmDialog.vue'
import FormField from '../../Components/FormField.vue'
import PageHeader from '../../Components/PageHeader.vue'

const props = defineProps({
  profile: { type: Object, required: true },
  logo: { type: Object, default: null },
  photoCount: { type: Number, default: 0 },
})

const form = useForm({
  shop_name: props.profile.shop_name || '',
  email: props.profile.email || '',
  phone: props.profile.phone || '',
  shop_address: props.profile.shop_address || '',
  motto: props.profile.motto || '',
  services_provided: props.profile.services_provided || '',
  logo: null,
})

const errors = computed(() => form.errors)

const logoPreview = ref(null)
const confirmDelete = ref(null)
const deleting = ref(false)

function revoke(url) {
  if (url) URL.revokeObjectURL(url)
}

function onLogoChange(event) {
  const file = event.target.files?.[0] || null
  event.target.value = ''
  if (!file) return
  revoke(logoPreview.value)
  form.logo = file
  logoPreview.value = URL.createObjectURL(file)
}

function clearLogo() {
  revoke(logoPreview.value)
  logoPreview.value = null
  form.logo = null
}

function clearUploads() {
  clearLogo()
}

function resetForm() {
  form.reset()
  form.clearErrors()
  clearUploads()
}

function submit() {
  form.post('/shop/edit', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      clearUploads()
      // The saved values become the new baseline for "unsaved changes".
      form.defaults({ ...form.data(), logo: null })
    },
  })
}

function deleteImage() {
  if (!confirmDelete.value) return
  deleting.value = true
  router.delete(`/shop/images/${confirmDelete.value.id}`, {
    preserveScroll: true,
    preserveState: true,
    onFinish: () => {
      deleting.value = false
      confirmDelete.value = null
    },
  })
}

onBeforeUnmount(clearUploads)
</script>
