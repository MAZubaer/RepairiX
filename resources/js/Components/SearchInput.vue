<template>
  <div class="relative w-full">
    <Search class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
    <input
      :value="modelValue"
      type="search"
      class="input pl-10"
      :placeholder="placeholder"
      :aria-label="placeholder"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>

<script setup>
import { Search } from 'lucide-vue-next'

defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Search…' },
})
defineEmits(['update:modelValue'])
</script>
