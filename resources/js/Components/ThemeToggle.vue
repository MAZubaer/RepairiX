<template>
  <button
    type="button"
    class="btn btn-ghost relative size-9 p-0"
    :aria-label="isDark ? 'Switch to light mode' : 'Switch to night mode'"
    :title="isDark ? 'Light mode' : 'Night mode'"
    :aria-pressed="isDark"
    @click="toggleTheme"
  >
    <Transition
      mode="out-in"
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -rotate-90 scale-50"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0 rotate-90 scale-50"
    >
      <Sun v-if="isDark" key="sun" class="size-[18px] text-amber-300" />
      <Moon v-else key="moon" class="size-[18px]" />
    </Transition>
  </button>
</template>

<script setup>
import { Moon, Sun } from 'lucide-vue-next'
import { isDark, toggleTheme } from '../lib/theme'
</script>
