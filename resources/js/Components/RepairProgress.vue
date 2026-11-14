<template>
  <div v-if="status === 'rejected'" class="flex items-center gap-3 rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 ring-1 ring-rose-200">
    <CircleX class="size-5 shrink-0" />
    The shop declined this request.
  </div>
  <ol v-else class="grid grid-cols-6 gap-1" :aria-label="`Progress: ${current + 1} of ${STEPS.length}`">
    <li v-for="(step, i) in STEPS" :key="step.status" class="flex flex-col gap-2">
      <span
        class="h-1.5 rounded-full transition-colors"
        :class="i < current ? 'bg-brand-500' : i === current ? (current === STEPS.length - 1 ? 'bg-emerald-500' : 'bg-brand-500 animate-pulse') : 'bg-slate-200'"
      />
      <span
        class="hidden text-[11px] leading-tight font-semibold sm:block"
        :class="i <= current ? 'text-slate-700' : 'text-slate-400'"
      >{{ step.label }}</span>
    </li>
  </ol>
</template>

<script setup>
import { computed } from 'vue'
import { CircleX } from 'lucide-vue-next'
import { STEPS, stepIndex } from '../lib/status'

const props = defineProps({
  status: { type: String, required: true },
})

const current = computed(() => stepIndex(props.status))
</script>
