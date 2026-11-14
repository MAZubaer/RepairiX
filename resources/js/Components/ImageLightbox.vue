<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-150"
      leave-to-class="opacity-0"
    >
      <div
        v-if="index !== null && images[index]"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 p-4 backdrop-blur"
        role="dialog"
        aria-modal="true"
        aria-label="Image viewer"
        @click.self="$emit('close')"
      >
        <button type="button" class="absolute top-4 right-4 rounded-full bg-white/10 p-2.5 text-white hover:bg-white/20" aria-label="Close" @click="$emit('close')">
          <X class="size-5" />
        </button>
        <button
          v-if="images.length > 1"
          type="button"
          class="absolute left-3 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 sm:left-6"
          aria-label="Previous image"
          @click="step(-1)"
        >
          <ChevronLeft class="size-6" />
        </button>
        <img :src="images[index].url" alt="Shop photo" class="max-h-[85vh] max-w-full animate-fade-in rounded-2xl object-contain shadow-2xl" />
        <button
          v-if="images.length > 1"
          type="button"
          class="absolute right-3 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 sm:right-6"
          aria-label="Next image"
          @click="step(1)"
        >
          <ChevronRight class="size-6" />
        </button>
        <p class="absolute bottom-5 rounded-full bg-white/10 px-3 py-1 text-sm font-medium text-white">{{ index + 1 }} / {{ images.length }}</p>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { onBeforeUnmount, onMounted } from 'vue'
import { ChevronLeft, ChevronRight, X } from 'lucide-vue-next'

const props = defineProps({
  images: { type: Array, default: () => [] },
  index: { type: Number, default: null },
})
const emit = defineEmits(['close', 'update:index'])

function step(delta) {
  const n = props.images.length
  emit('update:index', (props.index + delta + n) % n)
}

function onKey(e) {
  if (props.index === null) return
  if (e.key === 'Escape') emit('close')
  if (e.key === 'ArrowRight') step(1)
  if (e.key === 'ArrowLeft') step(-1)
}

onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
</script>
