<template>
  <Modal :show="show" size="sm" @close="$emit('cancel')">
    <div class="flex gap-4">
      <span class="grid size-11 shrink-0 place-items-center rounded-full" :class="danger ? 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400'">
        <TriangleAlert v-if="danger" class="size-5" />
        <Info v-else class="size-5" />
      </span>
      <div>
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ title }}</h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ message }}</p>
      </div>
    </div>
    <template #footer>
      <button type="button" class="btn btn-secondary" @click="$emit('cancel')">Cancel</button>
      <button type="button" class="btn" :class="danger ? 'btn-danger' : 'btn-primary'" :disabled="processing" @click="$emit('confirm')">
        {{ confirmLabel }}
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { Info, TriangleAlert } from 'lucide-vue-next'
import Modal from './Modal.vue'

defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, required: true },
  message: { type: String, default: '' },
  confirmLabel: { type: String, default: 'Confirm' },
  danger: { type: Boolean, default: false },
  processing: { type: Boolean, default: false },
})
defineEmits(['confirm', 'cancel'])
</script>
