<template>
  <div>
    <div v-if="images.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
      <button
        v-for="(img, i) in images"
        :key="img.image_id || i"
        type="button"
        class="group relative aspect-[4/3] overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-800"
        :aria-label="`Open photo ${i + 1}`"
        @click="open = i"
      >
        <img :src="img.thumb_url || img.url" :alt="`Shop photo ${i + 1}`" loading="lazy" class="size-full object-cover transition duration-300 group-hover:scale-105" />
        <span class="absolute inset-0 grid place-items-center bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/30 group-hover:opacity-100">
          <Expand class="size-6 text-white" />
        </span>
      </button>
    </div>
    <EmptyState v-else :icon="ImageIcon" title="No photos yet" :description="emptyText" />

    <ImageLightbox :images="images" :index="open" @update:index="open = $event" @close="open = null" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Expand, Image as ImageIcon } from 'lucide-vue-next'
import EmptyState from './EmptyState.vue'
import ImageLightbox from './ImageLightbox.vue'

defineProps({
  images: { type: Array, default: () => [] },
  emptyText: { type: String, default: 'This shop has not added photos yet.' },
})

const open = ref(null)
</script>
