<template>
  <AppLayout title="Photos">
    <PageHeader title="Photos" subtitle="Show customers your shop and your work. Your first photo is the cover on your shop card.">
      <template #actions>
        <span class="chip bg-white text-slate-700 ring-1 ring-slate-200">
          <ImageIcon class="size-3.5" />
          {{ items.length }} / {{ limits.max_photos }} photos
        </span>
      </template>
    </PageHeader>

    <!-- Upload -->
    <section class="card mb-6 p-4 sm:p-5">
      <label
        v-if="!uploading"
        class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed px-6 py-10 text-center transition"
        :class="[
          dragOverZone ? 'border-brand-500 bg-brand-50' : 'border-slate-300 bg-slate-50/60 hover:border-brand-400 hover:bg-brand-50/40',
          remaining === 0 ? 'pointer-events-none opacity-60' : '',
        ]"
        @dragover.prevent="onZoneDragOver"
        @dragleave.prevent="dragOverZone = false"
        @drop.prevent="onZoneDrop"
      >
        <span class="grid size-14 place-items-center rounded-2xl bg-white text-brand-600 shadow-soft ring-1 ring-slate-200">
          <ImagePlus class="size-7" />
        </span>
        <div>
          <p class="font-semibold text-slate-900">
            <template v-if="remaining > 0">Drag photos here or <span class="text-brand-700 underline underline-offset-2">browse</span></template>
            <template v-else>You've reached the {{ limits.max_photos }}-photo limit</template>
          </p>
          <p class="mt-1 text-sm text-slate-500">
            JPG, PNG or WebP · up to {{ limits.max_file_mb }} MB each · {{ remaining }} more allowed
          </p>
        </div>
        <input type="file" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" :disabled="remaining === 0" @change="onPick" />
      </label>

      <!-- Upload in progress -->
      <div v-else class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200/70">
        <div class="flex items-center justify-between gap-4">
          <p class="flex items-center gap-2 text-sm font-semibold text-slate-800">
            <LoaderCircle class="size-4 animate-spin text-brand-600" />
            Uploading {{ pendingPreviews.length }} {{ pendingPreviews.length === 1 ? 'photo' : 'photos' }}…
          </p>
          <span class="text-sm font-bold text-brand-700">{{ progress }}%</span>
        </div>
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
          <div class="h-full rounded-full bg-brand-600 transition-all duration-200" :style="{ width: `${progress}%` }" />
        </div>
        <div class="mt-4 flex gap-2 overflow-x-auto">
          <img v-for="src in pendingPreviews" :key="src" :src="src" alt="" class="size-16 shrink-0 rounded-lg object-cover opacity-70 ring-1 ring-slate-200" />
        </div>
      </div>

      <div v-if="uploadError" class="mt-4 flex items-start gap-3 rounded-xl bg-rose-50 p-3.5 text-sm text-rose-700 ring-1 ring-rose-200">
        <CircleAlert class="mt-0.5 size-4 shrink-0" />
        {{ uploadError }}
      </div>
    </section>

    <!-- Gallery -->
    <section v-if="items.length" class="card p-4 sm:p-5">
      <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <p class="flex items-center gap-2 text-sm text-slate-500">
          <Move class="size-4" />
          Drag photos to reorder. Changes save automatically.
        </p>
        <span class="text-xs font-medium" :class="saving ? 'text-slate-500' : 'text-emerald-600'">
          <template v-if="saving">Saving order…</template>
          <template v-else-if="savedAt">Order saved</template>
        </span>
      </div>

      <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        <li
          v-for="(photo, index) in items"
          :key="photo.image_id"
          draggable="true"
          class="group relative aspect-[4/3] cursor-grab overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200 transition active:cursor-grabbing"
          :class="dragIndex === index ? 'scale-95 opacity-40 ring-2 ring-brand-500' : ''"
          @dragstart="onDragStart(index, $event)"
          @dragenter.prevent="onDragEnter(index)"
          @dragover.prevent
          @dragend="onDragEnd"
        >
          <img :src="photo.thumb_url" :alt="`Photo ${index + 1}`" loading="lazy" class="pointer-events-none size-full object-cover" />

          <span v-if="index === 0" class="absolute top-2 left-2 chip bg-slate-900/85 text-white backdrop-blur">
            <Star class="size-3" fill="currentColor" />
            Cover
          </span>

          <!-- Actions: always visible on touch screens, on hover for mouse users -->
          <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-gradient-to-t from-slate-950/75 to-transparent p-2 opacity-100 transition sm:opacity-0 sm:group-hover:opacity-100 sm:focus-within:opacity-100">
            <div class="flex gap-1">
              <button type="button" class="photo-btn" aria-label="Move left" :disabled="index === 0" @click="move(index, -1)">
                <ChevronLeft class="size-4" />
              </button>
              <button type="button" class="photo-btn" aria-label="Move right" :disabled="index === items.length - 1" @click="move(index, 1)">
                <ChevronRight class="size-4" />
              </button>
            </div>
            <div class="flex gap-1">
              <button v-if="index !== 0" type="button" class="photo-btn" aria-label="Make cover photo" title="Make cover" @click="makeCover(index)">
                <Star class="size-4" />
              </button>
              <button type="button" class="photo-btn" aria-label="View full size" title="View" @click="viewing = index">
                <Expand class="size-4" />
              </button>
              <button type="button" class="photo-btn hover:bg-rose-600!" aria-label="Delete photo" title="Delete" @click="pendingDelete = photo">
                <Trash2 class="size-4" />
              </button>
            </div>
          </div>
        </li>
      </ul>
    </section>

    <div v-else-if="!uploading" class="card">
      <EmptyState
        :icon="Camera"
        title="No photos yet"
        description="Shops with photos get noticed. Add pictures of your counter, tools and finished repairs."
      />
    </div>

    <ImageLightbox :images="items" :index="viewing" @update:index="viewing = $event" @close="viewing = null" />

    <ConfirmDialog
      :show="!!pendingDelete"
      danger
      title="Delete this photo?"
      message="It will be removed from your shop page. This can't be undone."
      confirm-label="Delete photo"
      :processing="deleting"
      @cancel="pendingDelete = null"
      @confirm="deletePhoto"
    />
  </AppLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import {
  Camera, ChevronLeft, ChevronRight, CircleAlert, Expand, Image as ImageIcon, ImagePlus, LoaderCircle, Move, Star, Trash2,
} from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import ConfirmDialog from '../../Components/ConfirmDialog.vue'
import EmptyState from '../../Components/EmptyState.vue'
import ImageLightbox from '../../Components/ImageLightbox.vue'
import PageHeader from '../../Components/PageHeader.vue'

const props = defineProps({
  photos: { type: Array, default: () => [] },
  limits: { type: Object, default: () => ({ max_photos: 20, max_file_mb: 5 }) },
  storage: { type: String, default: 'local' },
})

const ACCEPTED = ['image/jpeg', 'image/png', 'image/webp']
const page = usePage()

// Local copy so drag-and-drop can reorder instantly; reset whenever the server sends new photos.
const items = ref([...props.photos])
watch(() => props.photos, (photos) => { items.value = [...photos] })

const remaining = computed(() => Math.max(0, props.limits.max_photos - items.value.length))

// --- Uploading ---------------------------------------------------------------
const uploading = ref(false)
const progress = ref(0)
const pendingPreviews = ref([])
const clientError = ref('')
const dragOverZone = ref(false)

const uploadError = computed(() => {
  if (clientError.value) return clientError.value
  const errors = page.props.errors || {}
  const key = Object.keys(errors).find((k) => k === 'photos' || k.startsWith('photos.'))
  return key ? errors[key] : ''
})

function onZoneDragOver(event) {
  // Only react to files dragged in from outside, not to photos being reordered.
  if (event.dataTransfer?.types?.includes('Files')) dragOverZone.value = true
}

function onZoneDrop(event) {
  dragOverZone.value = false
  upload(event.dataTransfer?.files)
}

function onPick(event) {
  upload(event.target.files)
  event.target.value = ''
}

function upload(fileList) {
  clientError.value = ''
  const files = Array.from(fileList || [])
  if (!files.length) return

  const wrongType = files.find((f) => !ACCEPTED.includes(f.type))
  if (wrongType) {
    clientError.value = `“${wrongType.name}” isn't a JPG, PNG or WebP image.`
    return
  }
  const tooBig = files.find((f) => f.size > props.limits.max_file_mb * 1024 * 1024)
  if (tooBig) {
    clientError.value = `“${tooBig.name}” is larger than ${props.limits.max_file_mb} MB.`
    return
  }
  if (files.length > remaining.value) {
    clientError.value = remaining.value
      ? `You can add ${remaining.value} more photo${remaining.value === 1 ? '' : 's'}. You selected ${files.length}.`
      : `You've reached the ${props.limits.max_photos}-photo limit.`
    return
  }

  pendingPreviews.value = files.map((f) => URL.createObjectURL(f))
  uploading.value = true
  progress.value = 0

  router.post('/shop/photos', { photos: files }, {
    forceFormData: true,
    preserveScroll: true,
    onProgress: (event) => {
      if (event?.percentage !== undefined) progress.value = Math.min(99, Math.round(event.percentage))
    },
    onFinish: () => {
      uploading.value = false
      pendingPreviews.value.forEach((src) => URL.revokeObjectURL(src))
      pendingPreviews.value = []
    },
  })
}

// --- Reordering --------------------------------------------------------------
const dragIndex = ref(null)
const saving = ref(false)
const savedAt = ref(null)
let orderAtDragStart = ''

const orderKey = () => items.value.map((p) => p.image_id).join(',')

function onDragStart(index, event) {
  dragIndex.value = index
  orderAtDragStart = orderKey()
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', String(items.value[index].image_id))
}

function onDragEnter(index) {
  if (dragIndex.value === null || dragIndex.value === index) return
  const list = [...items.value]
  const [moved] = list.splice(dragIndex.value, 1)
  list.splice(index, 0, moved)
  items.value = list
  dragIndex.value = index
}

function onDragEnd() {
  dragIndex.value = null
  if (orderKey() !== orderAtDragStart) saveOrder()
}

function move(index, delta) {
  const target = index + delta
  if (target < 0 || target >= items.value.length) return
  const list = [...items.value]
  ;[list[index], list[target]] = [list[target], list[index]]
  items.value = list
  saveOrder()
}

function makeCover(index) {
  const list = [...items.value]
  const [photo] = list.splice(index, 1)
  items.value = [photo, ...list]
  saveOrder()
}

function saveOrder() {
  saving.value = true
  router.post('/shop/photos/reorder', { order: items.value.map((p) => p.image_id) }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => { savedAt.value = Date.now() },
    onFinish: () => { saving.value = false },
  })
}

// --- Viewing & deleting -----------------------------------------------------
const viewing = ref(null)
const pendingDelete = ref(null)
const deleting = ref(false)

function deletePhoto() {
  if (!pendingDelete.value) return
  deleting.value = true
  router.delete(`/shop/images/${pendingDelete.value.image_id}`, {
    preserveScroll: true,
    preserveState: true,
    onFinish: () => {
      deleting.value = false
      pendingDelete.value = null
    },
  })
}

onBeforeUnmount(() => pendingPreviews.value.forEach((src) => URL.revokeObjectURL(src)))
</script>

<style scoped>
.photo-btn {
  display: grid;
  place-items: center;
  width: 2rem;
  height: 2rem;
  border-radius: 0.5rem;
  background: rgb(255 255 255 / 0.18);
  color: #fff;
  backdrop-filter: blur(6px);
  transition: background-color 0.15s ease;
}
.photo-btn:hover:not(:disabled) {
  background: rgb(255 255 255 / 0.32);
}
.photo-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}
</style>
