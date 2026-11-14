<template>
  <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
      <p v-if="eyebrow" class="mb-2 text-sm font-semibold text-brand-600 dark:text-brand-400">{{ eyebrow }}</p>
      <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl">{{ title }}</h1>
      <p v-if="subtitle" class="mt-1.5 max-w-2xl text-sm text-slate-500 dark:text-slate-400 sm:text-base">{{ subtitle }}</p>
    </div>
    <div v-if="$slots.actions" class="flex shrink-0 flex-wrap items-center gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  eyebrow: { type: String, default: '' },
})
</script>
