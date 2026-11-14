<template>
  <Head :title="title" />
  <div class="grid min-h-screen bg-white lg:grid-cols-[1fr_minmax(0,560px)] xl:grid-cols-[1fr_minmax(0,640px)]">
    <!-- Brand panel -->
    <aside class="relative hidden overflow-hidden bg-slate-950 lg:flex lg:flex-col lg:justify-between lg:p-12">
      <div class="pointer-events-none absolute -top-32 -left-24 size-[480px] rounded-full bg-brand-500/30 blur-3xl" />
      <div class="pointer-events-none absolute -right-24 bottom-0 size-[420px] rounded-full bg-sky-500/20 blur-3xl" />
      <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.06)_1px,transparent_1px)] [background-size:22px_22px]" />

      <AppLogo light class="relative" />

      <div class="relative max-w-md">
        <h2 class="text-4xl leading-tight font-extrabold tracking-tight text-white">{{ heading }}</h2>
        <p class="mt-4 text-lg text-slate-300">{{ tagline }}</p>
        <ul class="mt-10 space-y-4">
          <li v-for="point in points" :key="point" class="flex items-start gap-3 text-slate-200">
            <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-brand-500/20 text-brand-300">
              <Check class="size-3.5" stroke-width="3" />
            </span>
            {{ point }}
          </li>
        </ul>
      </div>

      <p class="relative text-sm text-slate-500">© {{ year }} RepairiX</p>
    </aside>

    <!-- Form panel -->
    <main class="flex flex-col">
      <div class="flex items-center justify-between px-6 py-5 sm:px-10">
        <AppLogo class="lg:invisible" />
        <Link href="/" class="btn btn-ghost btn-sm">
          <ArrowLeft class="size-4" />
          Back to home
        </Link>
      </div>
      <div class="flex flex-1 items-center justify-center px-6 pb-12 sm:px-10">
        <div class="w-full max-w-md animate-slide-up">
          <slot />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ArrowLeft, Check } from 'lucide-vue-next'
import AppLogo from '../Components/AppLogo.vue'

defineProps({
  title: { type: String, default: '' },
  heading: { type: String, default: 'Phone repair, without the guesswork.' },
  tagline: { type: String, default: 'Find trusted shops, follow every step of your repair, and pay a clear price.' },
  points: {
    type: Array,
    default: () => [
      'Browse verified repair shops near you',
      'Track your repair live, from request to delivery',
      'Transparent diagnosis and pricing up front',
    ],
  },
})

const year = new Date().getFullYear()
</script>
