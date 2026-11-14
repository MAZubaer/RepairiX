<template>
  <div class="-mx-1 flex gap-1 overflow-x-auto px-1 pb-1" role="tablist">
    <button
      v-for="tab in tabs"
      :key="tab.value"
      type="button"
      role="tab"
      :aria-selected="modelValue === tab.value"
      class="inline-flex shrink-0 items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-semibold transition"
      :class="modelValue === tab.value ? 'bg-slate-900 text-white shadow-sm dark:bg-brand-500/15 dark:text-brand-200 dark:ring-1 dark:ring-brand-400/30' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 ring-1 ring-slate-200 dark:ring-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40'"
      @click="$emit('update:modelValue', tab.value)"
    >
      {{ tab.label }}
      <span
        v-if="tab.count !== undefined"
        class="rounded-md px-1.5 py-0.5 text-[11px] font-bold"
        :class="modelValue === tab.value ? 'bg-white/20 text-white dark:bg-brand-400/20 dark:text-brand-100' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'"
      >{{ tab.count }}</span>
    </button>
  </div>
</template>

<script setup>
defineProps({
  tabs: { type: Array, required: true },
  modelValue: { type: String, required: true },
})
defineEmits(['update:modelValue'])
</script>
