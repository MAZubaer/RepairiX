<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <ShopNavbar />

    <main class="flex-1 flex items-start justify-center py-10">
      <div class="w-full max-w-3xl px-6">
        <h1 class="text-4xl font-extrabold mb-6">Edit Profile</h1>

        <div v-if="successMessage" class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
          {{ successMessage }}
        </div>

        <form @submit.prevent="submit" class="space-y-5 bg-white p-6 rounded-lg shadow-sm border">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold mb-1">Shop Name</label>
              <input v-model="form.shop_name" type="text" class="form-input" required />
              <div v-if="errors.shop_name" class="text-red-500 text-sm mt-1">{{ errors.shop_name[0] }}</div>
            </div>

            <div>
              <label class="block text-sm font-semibold mb-1">Business Email</label>
              <input v-model="form.email" type="email" class="form-input" required />
              <div v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email[0] }}</div>
            </div>

            <div>
              <label class="block text-sm font-semibold mb-1">Phone Number</label>
              <input v-model="form.phone" type="text" class="form-input" required />
              <div v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone[0] }}</div>
            </div>

            <div>
              <label class="block text-sm font-semibold mb-1">Shop Address</label>
              <input v-model="form.shop_address" type="text" class="form-input" required />
              <div v-if="errors.shop_address" class="text-red-500 text-sm mt-1">{{ errors.shop_address[0] }}</div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Shop Motto</label>
            <input v-model="form.motto" type="text" class="form-input" placeholder="Enter shop motto" />
            <div v-if="errors.motto" class="text-red-500 text-sm mt-1">{{ errors.motto[0] }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Services Provided</label>
            <textarea v-model="form.services_provided" rows="5" class="form-input" placeholder="Describe services offered"></textarea>
            <div v-if="errors.services_provided" class="text-red-500 text-sm mt-1">{{ errors.services_provided[0] }}</div>
          </div>

          <div class="rounded-md border border-dashed border-gray-300 p-4">
            <label class="block text-sm font-semibold mb-2">Shop Logo</label>
            <div v-if="logo" class="mb-3">
              <div class="inline-flex flex-col items-start gap-2">
                <img :src="logo.url" alt="Current logo" class="h-20 w-20 object-cover rounded-md border" />
                <button type="button" class="btn-danger text-xs" @click="deleteLogo">Delete Logo</button>
              </div>
            </div>
            <input type="file" accept="image/png,image/jpeg,image/jpg,image/webp" @change="onLogoChange" class="block w-full text-sm" />
            <div v-if="errors.logo" class="text-red-500 text-sm mt-1">{{ errors.logo[0] }}</div>
          </div>

          <div class="rounded-md border border-dashed border-gray-300 p-4">
            <label class="block text-sm font-semibold mb-2">Shop Images</label>
            <div v-if="gallery.length" class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
              <div v-for="img in gallery" :key="img.image_id" class="relative">
                <img :src="img.url" alt="Gallery image" class="h-24 w-full object-cover rounded-md border" />
                <button
                  type="button"
                  class="btn-danger absolute right-2 top-2 text-xs"
                  @click="deleteGalleryImage(img.image_id)"
                >
                  Delete
                </button>
              </div>
            </div>
            <input type="file" multiple accept="image/png,image/jpeg,image/jpg,image/webp" @change="onGalleryChange" class="block w-full text-sm" />
            <div v-if="errors.gallery_images" class="text-red-500 text-sm mt-1">{{ errors.gallery_images[0] }}</div>
            <div v-if="errors['gallery_images.0']" class="text-red-500 text-sm mt-1">{{ errors['gallery_images.0'][0] }}</div>
          </div>

          <div class="flex gap-3">
            <button type="submit" class="btn-primary" :disabled="form.processing">Save Changes</button>
            <a href="/dashboard/shop" class="inline-flex items-center px-4 py-2 rounded-md border text-sm">Cancel</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { useForm, usePage } from '@inertiajs/inertia-vue3'
import ShopNavbar from '../../Components/ShopNavbar.vue'

const props = defineProps({
  profile: { type: Object, required: true },
  logo: { type: Object, default: null },
  gallery: { type: Array, default: () => [] },
})

const page = usePage()
const errors = computed(() => page.props.value.errors || {})
const successMessage = ref('')

const form = useForm({
  shop_name: props.profile.shop_name || '',
  email: props.profile.email || '',
  phone: props.profile.phone || '',
  shop_address: props.profile.shop_address || '',
  motto: props.profile.motto || '',
  services_provided: props.profile.services_provided || '',
  logo: null,
  gallery_images: [],
})

function onLogoChange(event) {
  const file = event.target.files && event.target.files[0] ? event.target.files[0] : null
  form.logo = file
}

function onGalleryChange(event) {
  const files = event.target.files ? Array.from(event.target.files) : []
  form.gallery_images = files
}

function submit() {
  successMessage.value = ''
  form.post('/shop/edit', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Changes saved successfully.'
    },
    onError: () => {
      successMessage.value = ''
    },
  })
}

function deleteLogo() {
  if (!props.logo || !props.logo.image_id) {
    return
  }

  if (!window.confirm('Delete current logo?')) {
    return
  }

  successMessage.value = ''
  Inertia.delete(`/shop/images/${props.logo.image_id}`, {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Logo deleted successfully.'
    },
  })
}

function deleteGalleryImage(imageId) {
  if (!imageId) {
    return
  }

  if (!window.confirm('Delete this shop image?')) {
    return
  }

  successMessage.value = ''
  Inertia.delete(`/shop/images/${imageId}`, {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Shop image deleted successfully.'
    },
  })
}
</script>

<style scoped>
.form-input{ width:100%; padding:.75rem 1rem; border:1px solid rgba(15,23,42,0.08); border-radius:.5rem; }
.form-input:focus{ outline:none; border-color: var(--color-primary); box-shadow: 0 0 0 4px rgba(20,184,166,0.08); }
.btn-danger{ display:inline-flex; align-items:center; justify-content:center; padding:.3rem .5rem; border-radius:.5rem; background:#ef4444; color:#fff; font-weight:700; }
.btn-danger:hover{ opacity:.92; }
</style>
