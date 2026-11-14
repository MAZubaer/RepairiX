<template>
  <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
    <span class="grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
      <component :is="icon" class="size-7" />
    </span>
    <h3 class="mt-4 text-base font-bold text-slate-900">{{ title }}</h3>
    <p v-if="description" class="mt-1 max-w-sm text-sm text-slate-500">{{ description }}</p>
    <div v-if="$slots.default" class="mt-5">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { Inbox } from 'lucide-vue-next'

defineProps({
  icon: { type: [Object, Function], default: () => Inbox },
  title: { type: String, required: true },
  description: { type: String, default: '' },
})
</script>
