<template>
  <AppLayout title="About" bare>
    <section class="relative overflow-hidden bg-white dark:bg-slate-900">
      <div class="pointer-events-none absolute -top-40 left-1/2 size-[600px] -translate-x-1/2 rounded-full bg-brand-100/60 dark:bg-brand-500/15 blur-3xl" />
      <div class="container-page relative py-20 text-center sm:py-24">
        <span class="eyebrow">About RepairiX</span>
        <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 sm:text-5xl">
          Making device repair honest, simple and trackable.
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-600 dark:text-slate-400">
          We connect people who need a repair with professional shops nearby, and give both sides one clear place to manage the job.
        </p>
      </div>
    </section>

    <section class="py-16 sm:py-20">
      <div class="container-page grid gap-6 md:grid-cols-2">
        <article class="card p-8">
          <span class="grid size-12 place-items-center rounded-2xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400">
            <Target class="size-6" />
          </span>
          <h2 class="mt-6 text-2xl font-bold text-slate-900 dark:text-slate-100">Our mission</h2>
          <p class="mt-3 leading-relaxed text-slate-600 dark:text-slate-400">
            To simplify repairing your digital devices (phones, laptops, tablets and accessories) by connecting you with
            verified, professional repair shops in your area, with no guesswork about what's wrong or what it will cost.
          </p>
        </article>
        <article class="card p-8">
          <span class="grid size-12 place-items-center rounded-2xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400">
            <Heart class="size-6" />
          </span>
          <h2 class="mt-6 text-2xl font-bold text-slate-900 dark:text-slate-100">Why choose us</h2>
          <p class="mt-3 leading-relaxed text-slate-600 dark:text-slate-400">
            RepairiX gives customers and shops one seamless platform: transparent pricing, quality service, and dependable
            turnaround, with every status change visible as it happens.
          </p>
        </article>
      </div>

      <div class="container-page mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="feature in features" :key="feature.title" class="card card-hover p-6">
          <component :is="feature.icon" class="size-7 text-brand-600 dark:text-brand-400" />
          <h3 class="mt-5 font-bold text-slate-900 dark:text-slate-100">{{ feature.title }}</h3>
          <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">{{ feature.text }}</p>
        </div>
      </div>
    </section>

    <section v-if="!user" class="pb-20">
      <div class="container-page">
        <div class="card flex flex-col items-center justify-between gap-6 bg-gradient-to-r from-brand-600 to-brand-700 p-10 text-center ring-0 sm:flex-row sm:text-left">
          <div>
            <h2 class="text-2xl font-extrabold text-white">Ready to get started?</h2>
            <p class="mt-1 text-brand-100">Create a free customer account or bring your shop online.</p>
          </div>
          <div class="flex flex-col gap-3 sm:flex-row">
            <Link href="/register/customer" class="btn btn-lg bg-white dark:bg-slate-900 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-500/10">I need a repair</Link>
            <Link href="/register/shop" class="btn btn-lg bg-brand-800/60 text-white ring-1 ring-white/30 hover:bg-brand-800">I run a shop</Link>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { BadgeCheck, Heart, MonitorSmartphone, Target, Wrench, Zap } from 'lucide-vue-next'
import AppLayout from '../Layouts/AppLayout.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)

const features = [
  { icon: MonitorSmartphone, title: 'Every device', text: 'Phones, laptops, tablets and accessories, all in one place.' },
  { icon: Wrench, title: 'Expert repairs', text: 'Verified and skilled technicians.' },
  { icon: Zap, title: 'Fast service', text: 'Quick turnaround with live progress.' },
  { icon: BadgeCheck, title: 'Rated by customers', text: 'Every delivered repair can be reviewed.' },
]
</script>
