<template>
  <Head :title="title" />
  <div class="flex min-h-screen flex-col bg-slate-50">
    <AppNav />
    <main class="flex-1" :class="bare ? '' : 'py-8 sm:py-10'">
      <div v-if="!bare" class="container-page animate-slide-up">
        <slot />
      </div>
      <slot v-else />
    </main>
    <AppFooter />
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AppNav from '../Components/AppNav.vue'
import AppFooter from '../Components/AppFooter.vue'

defineProps({
  title: { type: String, default: '' },
  /** Full-bleed pages (landing, about) manage their own containers. */
  bare: { type: Boolean, default: false },
})
</script>
