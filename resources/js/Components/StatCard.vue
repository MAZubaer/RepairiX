<template>
  <div class="card p-5">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ label }}</p>
        <p class="mt-2 truncate text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl">{{ value }}</p>
      </div>
      <span v-if="icon" class="grid size-11 shrink-0 place-items-center rounded-xl" :class="toneClass">
        <component :is="icon" class="size-5" />
      </span>
    </div>
    <p v-if="hint" class="mt-3 text-xs text-slate-500 dark:text-slate-400">{{ hint }}</p>
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
  brand: 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400',
  amber: 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400',
  indigo: 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400',
  emerald: 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
  rose: 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400',
  sky: 'bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400',
  violet: 'bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400',
  slate: 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
}[props.tone] || 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400'))
</script>
