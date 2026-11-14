<template>
  <span class="chip ring-1 ring-inset" :class="meta.badge">
    <span class="size-1.5 rounded-full" :class="meta.dot" />
    {{ meta.label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { statusMeta } from '../lib/status'

const props = defineProps({
  status: { type: String, default: '' },
})

const meta = computed(() => statusMeta(props.status))
</script>
