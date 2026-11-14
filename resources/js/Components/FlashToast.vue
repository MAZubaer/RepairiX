<template>
  <Teleport to="body">
    <div class="pointer-events-none fixed inset-x-0 bottom-0 z-50 flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6">
      <TransitionGroup
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-3 opacity-0"
        leave-active-class="transition duration-200 ease-in"
        leave-to-class="opacity-0"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl bg-white dark:bg-slate-900 p-4 shadow-lift ring-1 ring-slate-200 dark:ring-slate-800"
          role="status"
        >
          <CircleCheck v-if="toast.type === 'success'" class="mt-0.5 size-5 shrink-0 text-emerald-500" />
          <CircleAlert v-else class="mt-0.5 size-5 shrink-0 text-rose-500" />
          <p class="flex-1 text-sm font-medium text-slate-800 dark:text-slate-200">{{ toast.message }}</p>
          <button type="button" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-400" aria-label="Dismiss" @click="dismiss(toast.id)">
            <X class="size-4" />
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { CircleAlert, CircleCheck, X } from 'lucide-vue-next'

// Page-level (non-field) errors are shown as toasts; field errors stay next to their inputs.
const TOAST_ERROR_KEYS = ['status', 'rating', 'billing']

const page = usePage()
const toasts = ref([])
let nextId = 1

function push(type, message) {
  if (!message) return
  const id = nextId++
  toasts.value.push({ id, type, message })
  setTimeout(() => dismiss(id), 4500)
}

function dismiss(id) {
  toasts.value = toasts.value.filter((t) => t.id !== id)
}

// page.props is replaced on every visit, so this runs once per server response.
watch(
  () => page.props,
  (props) => {
    push('success', props?.flash?.success)
    TOAST_ERROR_KEYS.forEach((key) => push('error', props?.errors?.[key]))
  },
  { immediate: true },
)
</script>
