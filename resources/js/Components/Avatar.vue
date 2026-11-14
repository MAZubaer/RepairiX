<template>
  <span
    class="inline-flex shrink-0 items-center justify-center overflow-hidden font-bold"
    :class="[sizeClass, rounded ? 'rounded-2xl' : 'rounded-full', src ? 'bg-white ring-1 ring-slate-200' : avatarTone(name)]"
  >
    <img v-if="src" :src="src" :alt="`${name} logo`" class="size-full object-cover" />
    <span v-else>{{ initials(name) }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { avatarTone, initials } from '../lib/format'

const props = defineProps({
  name: { type: String, default: '' },
  src: { type: String, default: null },
  size: { type: String, default: 'md' },
  rounded: { type: Boolean, default: false },
})

const sizeClass = computed(() => ({
  sm: 'size-8 text-xs',
  md: 'size-10 text-sm',
  lg: 'size-14 text-lg',
  xl: 'size-24 text-3xl',
}[props.size] || 'size-10 text-sm'))
</script>
