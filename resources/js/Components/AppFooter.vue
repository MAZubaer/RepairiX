<template>
  <footer class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
    <div class="container-page flex flex-col items-center justify-between gap-4 py-8 sm:flex-row">
      <div class="flex items-center gap-3">
        <img :src="logoSrc" alt="" class="size-7 rounded-lg" />
        <p class="text-sm text-slate-500 dark:text-slate-400">© {{ year }} RepairiX. Your all-in-one repair solution.</p>
      </div>
      <nav class="flex items-center gap-5 text-sm font-medium text-slate-500 dark:text-slate-400">
        <Link href="/about" class="hover:text-slate-900 dark:hover:text-slate-100">About</Link>
        <Link v-if="!user" href="/register/shop" class="hover:text-slate-900 dark:hover:text-slate-100">For repair shops</Link>
        <Link v-if="!user" href="/login" class="hover:text-slate-900 dark:hover:text-slate-100">Log in</Link>
      </nav>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)
const year = new Date().getFullYear()
const logoSrc = '/images/repairix_short.png'
</script>
