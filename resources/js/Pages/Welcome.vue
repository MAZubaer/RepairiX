<template>
  <AppLayout bare>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-white dark:bg-slate-900">
      <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(rgba(15,23,42,0.06)_1px,transparent_1px)] dark:bg-[radial-gradient(rgba(255,255,255,0.06)_1px,transparent_1px)] [background-size:24px_24px] [mask-image:linear-gradient(to_bottom,black,transparent_85%)]" />
      <div class="pointer-events-none absolute -top-40 right-0 size-[560px] rounded-full bg-brand-200/50 dark:bg-brand-500/20 blur-3xl" />
      <div class="pointer-events-none absolute top-40 -left-40 size-[420px] rounded-full bg-sky-100/70 dark:bg-sky-500/15 blur-3xl" />

      <div class="container-page relative grid items-center gap-14 py-16 sm:py-24 lg:grid-cols-2 lg:py-28">
        <div class="animate-slide-up">
          <span class="eyebrow">
            <Sparkles class="size-3.5" />
            Device repair, simplified
          </span>
          <h1 class="mt-6 text-4xl leading-[1.08] font-extrabold tracking-tight text-slate-900 dark:text-slate-100 sm:text-5xl lg:text-6xl">
            Get your devices fixed by shops
            <span class="bg-gradient-to-r from-brand-600 to-sky-500 bg-clip-text dark:from-brand-400 dark:to-sky-400 text-transparent">you can trust.</span>
          </h1>
          <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600 dark:text-slate-400">
            Phones, laptops, tablets and accessories. RepairiX connects you with verified repair shops. Send a request in
            a minute, see the diagnosis and price, and follow your repair step by step until it's back in your hands.
          </p>

          <div class="mt-9 flex flex-col gap-3 sm:flex-row">
            <template v-if="user">
              <Link :href="dashboardHref" class="btn btn-primary btn-lg">
                Go to your dashboard
                <ArrowRight class="size-5" />
              </Link>
            </template>
            <template v-else>
              <Link href="/register/customer" class="btn btn-primary btn-lg">
                Find a repair shop
                <ArrowRight class="size-5" />
              </Link>
              <Link href="/register/shop" class="btn btn-secondary btn-lg">
                <Store class="size-5" />
                Register your shop
              </Link>
            </template>
          </div>

          <ul class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm font-medium text-slate-600 dark:text-slate-400">
            <li v-for="item in heroPoints" :key="item" class="flex items-center gap-2">
              <CircleCheck class="size-4 text-brand-600 dark:text-brand-400" />
              {{ item }}
            </li>
          </ul>
        </div>

        <!-- Product preview: a repair being tracked -->
        <div class="relative mx-auto w-full max-w-md lg:max-w-none">
          <div class="absolute -inset-4 rotate-2 rounded-[2rem] bg-gradient-to-br from-brand-100 to-sky-100 dark:from-brand-500/25 dark:to-sky-500/10" />
          <div class="card relative p-6 shadow-lift sm:p-7">
            <div class="flex items-start justify-between gap-4">
              <div class="flex items-center gap-3">
                <span class="grid size-12 place-items-center rounded-2xl bg-slate-900 text-white dark:bg-slate-800 dark:ring-1 dark:ring-slate-700">
                  <Laptop class="size-6" />
                </span>
                <div>
                  <p class="font-bold text-slate-900 dark:text-slate-100">MacBook Air M1</p>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Won't charge · Fix It Hub</p>
                </div>
              </div>
              <StatusBadge status="in progress" />
            </div>

            <ol class="mt-7 space-y-4">
              <li v-for="(step, i) in previewSteps" :key="step.label" class="flex items-center gap-3">
                <span
                  class="grid size-7 shrink-0 place-items-center rounded-full text-xs font-bold"
                  :class="i < 2 ? 'bg-brand-600 text-white' : i === 2 ? 'bg-brand-100 dark:bg-brand-500/15 text-brand-700 dark:text-brand-300 ring-4 ring-brand-50' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500'"
                >
                  <Check v-if="i < 2" class="size-3.5" stroke-width="3" />
                  <span v-else>{{ i + 1 }}</span>
                </span>
                <div class="flex flex-1 items-center justify-between gap-2">
                  <span class="text-sm font-semibold" :class="i <= 2 ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 dark:text-slate-500'">{{ step.label }}</span>
                  <span class="text-xs text-slate-400 dark:text-slate-500">{{ step.time }}</span>
                </div>
              </li>
            </ol>

            <div class="mt-7 grid grid-cols-2 gap-3 rounded-2xl bg-slate-50 dark:bg-slate-800/40 p-4 text-sm">
              <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Diagnosis</p>
                <p class="mt-0.5 font-semibold text-slate-800 dark:text-slate-200">Charging board replacement</p>
              </div>
              <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Quoted price</p>
                <p class="mt-0.5 font-semibold text-slate-800 dark:text-slate-200">৳6,500</p>
              </div>
            </div>
          </div>

          <div class="card absolute -bottom-6 -left-4 hidden items-center gap-3 px-4 py-3 shadow-lift sm:flex">
            <span class="grid size-9 place-items-center rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
              <Mail class="size-4" />
            </span>
            <div class="text-sm">
              <p class="font-semibold text-slate-800 dark:text-slate-200">Email sent</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">"Your repair is in progress"</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- What we fix -->
    <section class="pt-20 sm:pt-24">
      <div class="container-page">
        <div class="mx-auto max-w-2xl text-center">
          <p class="text-sm font-semibold text-brand-600 dark:text-brand-400">What we fix</p>
          <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 sm:text-4xl">One place for every device you own</h2>
        </div>
        <div class="mt-12 grid grid-cols-2 gap-4 lg:grid-cols-4 lg:gap-6">
          <div v-for="device in DEVICE_TYPES" :key="device.value" class="card card-hover p-6">
            <span class="grid size-12 place-items-center rounded-2xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400">
              <component :is="device.icon" class="size-6" />
            </span>
            <h3 class="mt-5 text-lg font-bold text-slate-900 dark:text-slate-100">{{ device.plural }}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ device.examples }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How it works -->
    <section class="py-20 sm:py-24">
      <div class="container-page">
        <div class="mx-auto max-w-2xl text-center">
          <p class="text-sm font-semibold text-brand-600 dark:text-brand-400">How it works</p>
          <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 sm:text-4xl">From broken to fixed in three steps</h2>
        </div>

        <div class="mt-14 grid gap-6 md:grid-cols-3">
          <div v-for="(step, i) in steps" :key="step.title" class="card card-hover p-7">
            <div class="flex items-center justify-between">
              <span class="grid size-12 place-items-center rounded-2xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400">
                <component :is="step.icon" class="size-6" />
              </span>
              <span class="text-5xl font-extrabold text-slate-100 dark:text-slate-800">0{{ i + 1 }}</span>
            </div>
            <h3 class="mt-6 text-lg font-bold text-slate-900 dark:text-slate-100">{{ step.title }}</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ step.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- For shops -->
    <section class="pb-20 sm:pb-24">
      <div class="container-page">
        <div class="relative overflow-hidden rounded-[2rem] bg-slate-950 px-6 dark:bg-slate-900 dark:ring-1 dark:ring-slate-800 py-14 sm:px-12 lg:px-16">
          <div class="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-brand-500/30 blur-3xl" />
          <div class="relative grid items-center gap-12 lg:grid-cols-2">
            <div>
              <p class="text-sm font-semibold text-brand-300">For repair shops</p>
              <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Run every repair job from one dashboard</h2>
              <p class="mt-4 text-lg text-slate-300">
                Receive requests, record your diagnosis and price, and move each job along. Customers are kept up to date automatically.
              </p>
              <Link href="/register/shop" class="btn btn-lg mt-8 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800">
                Register your shop
                <ArrowRight class="size-5" />
              </Link>
            </div>
            <ul class="grid gap-4 sm:grid-cols-2">
              <li v-for="feature in shopFeatures" :key="feature.title" class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                <component :is="feature.icon" class="size-6 text-brand-300" />
                <p class="mt-4 font-semibold text-white">{{ feature.title }}</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ feature.text }}</p>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  ArrowRight, BellRing, Check, CircleCheck, ClipboardList, Laptop, Mail, Search, Send,
  Sparkles, Star, Store, Truck,
} from 'lucide-vue-next'
import AppLayout from '../Layouts/AppLayout.vue'
import StatusBadge from '../Components/StatusBadge.vue'
import { DEVICE_TYPES } from '../lib/devices'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)
const dashboardHref = computed(() => ({ admin: '/admin/dashboard', shop: '/dashboard/shop' }[user.value?.role] || '/dashboard/customer'))

const heroPoints = ['Verified repair shops', 'Upfront diagnosis & price', 'Live status updates']

const previewSteps = [
  { label: 'Request sent', time: 'Mon 10:12' },
  { label: 'Accepted by shop', time: 'Mon 10:40' },
  { label: 'Repair in progress', time: 'Now' },
  { label: 'Ready & on its way', time: '' },
]

const steps = [
  { icon: Search, title: 'Find a shop', text: 'Browse active repair shops, compare ratings and look through their work.' },
  { icon: Send, title: 'Send a request', text: 'Pick your device, tell the shop the model and what is wrong. It takes about a minute.' },
  { icon: Truck, title: 'Track to delivery', text: 'See the diagnosis and price, follow each step, then accept delivery and rate the shop.' },
]

const shopFeatures = [
  { icon: ClipboardList, title: 'Request inbox', text: 'Accept or reject new jobs in one click.' },
  { icon: BellRing, title: 'Automatic updates', text: 'Customers are notified as the job moves.' },
  { icon: Store, title: 'Shop profile', text: 'Show your logo, services and gallery.' },
  { icon: Star, title: 'Build your rating', text: 'Earn reviews from every delivered repair.' },
]
</script>
