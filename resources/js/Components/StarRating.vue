<template>
  <div class="inline-flex items-center gap-1.5">
    <div
      class="flex items-center"
      :role="interactive ? 'radiogroup' : 'img'"
      :aria-label="interactive ? 'Choose a rating' : `Rated ${display.toFixed(1)} out of 5`"
      @mouseleave="hover = 0"
    >
      <component
        :is="interactive ? 'button' : 'span'"
        v-for="n in 5"
        :key="n"
        :type="interactive ? 'button' : undefined"
        :role="interactive ? 'radio' : undefined"
        :aria-checked="interactive ? n === modelValue : undefined"
        :aria-label="interactive ? `${n} star${n > 1 ? 's' : ''}` : undefined"
        class="relative"
        :class="interactive ? 'cursor-pointer p-0.5 transition hover:scale-110' : ''"
        @mouseenter="interactive && (hover = n)"
        @click="interactive && $emit('update:modelValue', n)"
      >
        <Star :class="[sizeClass, 'text-slate-200 dark:text-slate-700']" fill="currentColor" stroke-width="0" />
        <span class="absolute inset-0 overflow-hidden" :class="interactive ? 'p-0.5' : ''" :style="{ width: fillWidth(n) }">
          <Star :class="[sizeClass, 'text-amber-400']" fill="currentColor" stroke-width="0" />
        </span>
      </component>
    </div>
    <span v-if="showValue" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
      {{ display.toFixed(1) }}
      <span v-if="count !== null" class="font-normal text-slate-400 dark:text-slate-500">({{ count }})</span>
    </span>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Star } from 'lucide-vue-next'

const props = defineProps({
  modelValue: { type: Number, default: 0 },
  interactive: { type: Boolean, default: false },
  size: { type: String, default: 'sm' },
  showValue: { type: Boolean, default: false },
  count: { type: Number, default: null },
})
defineEmits(['update:modelValue'])

const hover = ref(0)
const display = computed(() => Math.max(0, Math.min(5, Number(hover.value || props.modelValue || 0))))
const sizeClass = computed(() => ({ xs: 'size-3.5', sm: 'size-4', md: 'size-5', lg: 'size-7' }[props.size] || 'size-4'))

function fillWidth(n) {
  const fraction = Math.max(0, Math.min(1, display.value - (n - 1)))
  if (props.interactive) return fraction > 0 ? '100%' : '0%'
  return `${fraction * 100}%`
}
</script>
