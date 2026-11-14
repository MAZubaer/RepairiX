<template>
  <ul class="flex flex-wrap gap-1.5" :aria-label="`Repairs ${devices.map((d) => d.plural.toLowerCase()).join(', ')}`">
    <li
      v-for="device in devices"
      :key="device.value"
      class="chip gap-1 bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
      :class="size === 'sm' ? 'px-2 py-0.5 text-[11px]' : ''"
    >
      <component :is="device.icon" :class="size === 'sm' ? 'size-3' : 'size-3.5'" />
      {{ device.plural }}
    </li>
  </ul>
</template>

<script setup>
import { computed } from 'vue'
import { devicesFor } from '../lib/devices'

const props = defineProps({
  types: { type: Array, default: () => [] },
  size: { type: String, default: 'md' },
})

const devices = computed(() => devicesFor(props.types))
</script>
