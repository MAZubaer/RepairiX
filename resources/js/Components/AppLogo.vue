<template>
  <Link :href="href" class="group inline-flex items-center gap-2.5" aria-label="RepairiX home">
    <img :src="logoSrc" alt="" class="size-9 rounded-xl object-contain transition group-hover:scale-105" />
    <span class="text-lg font-extrabold tracking-tight" :class="light ? 'text-white' : 'text-slate-900 dark:text-slate-100'">
      Repairi<span :class="light ? 'text-brand-300' : 'text-brand-600 dark:text-brand-400'">X</span>
    </span>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

// Served from /public, so bind it rather than letting Vite try to bundle it.
const logoSrc = '/images/repairix_short.png'

defineProps({
  href: { type: String, default: '/' },
  light: { type: Boolean, default: false },
})
</script>
