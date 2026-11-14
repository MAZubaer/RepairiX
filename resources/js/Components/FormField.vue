<template>
  <div>
    <div v-if="label" class="flex items-center justify-between">
      <label :for="id" class="label">
        {{ label }}
        <span v-if="optional" class="font-normal text-slate-400">(optional)</span>
      </label>
      <slot name="label-aside" />
    </div>
    <div class="relative">
      <span v-if="icon" class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
        <component :is="icon" class="size-4" />
      </span>
      <slot :id="id" :input-class="['input', icon ? 'pl-10' : '', error ? 'input-error' : '']" />
    </div>
    <p v-if="error" class="field-error">{{ error }}</p>
    <p v-else-if="hint" class="field-hint">{{ hint }}</p>
  </div>
</template>

<script setup>
defineProps({
  id: { type: String, default: undefined },
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  icon: { type: [Object, Function], default: null },
  optional: { type: Boolean, default: false },
})
</script>
