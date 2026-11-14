<template>
  <div class="card p-5">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="text-sm font-medium text-slate-500">{{ label }}</p>
        <p class="mt-2 truncate text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ value }}</p>
      </div>
      <span v-if="icon" class="grid size-11 shrink-0 place-items-center rounded-xl" :class="toneClass">
        <component :is="icon" class="size-5" />
      </span>
    </div>
    <p v-if="hint" class="mt-3 text-xs text-slate-500">{{ hint }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  icon: { type: [Object, Function], default: null },
  tone: { type: String, default: 'brand' },
  hint: { type: String, default: '' },
})

const toneClass = computed(() => ({
  brand: 'bg-brand-50 text-brand-600',
  amber: 'bg-amber-50 text-amber-600',
  indigo: 'bg-indigo-50 text-indigo-600',
  emerald: 'bg-emerald-50 text-emerald-600',
  rose: 'bg-rose-50 text-rose-600',
  sky: 'bg-sky-50 text-sky-600',
  violet: 'bg-violet-50 text-violet-600',
  slate: 'bg-slate-100 text-slate-600',
}[props.tone] || 'bg-brand-50 text-brand-600'))
</script>
