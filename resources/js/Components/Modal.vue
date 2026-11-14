<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-40 overflow-y-auto bg-slate-900/50 backdrop-blur-sm" @mousedown.self="close">
        <div class="flex min-h-full items-end justify-center p-0 sm:items-center sm:p-6" @mousedown.self="close">
          <div
            role="dialog"
            aria-modal="true"
            :aria-label="title"
            class="relative w-full animate-slide-up rounded-t-3xl bg-white shadow-lift sm:rounded-3xl"
            :class="widthClass"
          >
            <div v-if="title" class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
              <div>
                <h2 class="text-lg font-bold text-slate-900">{{ title }}</h2>
                <p v-if="subtitle" class="mt-0.5 text-sm text-slate-500">{{ subtitle }}</p>
              </div>
              <button type="button" class="btn btn-ghost -mt-1 -mr-2 p-2" aria-label="Close" @click="close">
                <X class="size-5" />
              </button>
            </div>
            <div class="px-6 py-5">
              <slot />
            </div>
            <div v-if="$slots.footer" class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4 sm:flex-row sm:justify-end sm:rounded-b-3xl">
              <slot name="footer" />
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, watch } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  size: { type: String, default: 'md' },
})
const emit = defineEmits(['close'])

const widthClass = computed(() => ({ sm: 'sm:max-w-md', md: 'sm:max-w-lg', lg: 'sm:max-w-2xl', xl: 'sm:max-w-4xl' }[props.size] || 'sm:max-w-lg'))

function close() {
  emit('close')
}

function onKey(e) {
  if (e.key === 'Escape') close()
}

watch(
  () => props.show,
  (open) => {
    document.body.style.overflow = open ? 'hidden' : ''
    if (open) window.addEventListener('keydown', onKey)
    else window.removeEventListener('keydown', onKey)
  },
)

onBeforeUnmount(() => {
  document.body.style.overflow = ''
  window.removeEventListener('keydown', onKey)
})
</script>
