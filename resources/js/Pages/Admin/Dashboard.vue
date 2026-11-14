<template>
  <AppLayout title="Admin overview">
    <PageHeader eyebrow="Admin" title="Platform overview" subtitle="Key numbers across shops, customers and repairs." />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <StatCard label="Repair shops" :value="formatNumber(stats.totalDealers)" :icon="Store" />
      <StatCard label="Customers" :value="formatNumber(stats.totalCustomers)" :icon="Users" tone="sky" />
      <StatCard label="Repairs completed" :value="formatNumber(stats.totalRepairsCompleted)" :icon="PackageCheck" tone="emerald" />
      <StatCard label="Total Repair Value" :value="formatMoney(stats.totalRevenue, '৳0')" :icon="Banknote" tone="amber" hint="Sum of costs on finished repairs" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
      <section class="card p-6">
        <h2 class="font-bold text-slate-900 dark:text-slate-100">Subscriptions</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Shops with an active plan.</p>
        <div class="mt-6 flex items-end gap-2">
          <span class="text-4xl font-extrabold text-slate-900 dark:text-slate-100">{{ stats.activeSubscriptions }}</span>
          <span class="mb-1 text-sm text-slate-500 dark:text-slate-400">of {{ stats.totalDealers }} shops</span>
        </div>
        <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
          <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-600 transition-all" :style="{ width: `${activeShare}%` }" />
        </div>
        <p class="mt-2 text-xs font-medium text-slate-500 dark:text-slate-400">{{ activeShare }}% active</p>
      </section>

      <section class="grid gap-4 sm:grid-cols-3 lg:col-span-2">
        <Link v-for="link in quickLinks" :key="link.href" :href="link.href" class="card card-hover group flex flex-col p-6">
          <span class="grid size-11 place-items-center rounded-xl bg-slate-900 text-white dark:bg-slate-800 dark:ring-1 dark:ring-slate-700">
            <component :is="link.icon" class="size-5" />
          </span>
          <p class="mt-5 font-bold text-slate-900 dark:text-slate-100">{{ link.title }}</p>
          <p class="mt-1 flex-1 text-sm text-slate-500 dark:text-slate-400">{{ link.text }}</p>
          <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700 dark:text-brand-300">
            Open
            <ArrowRight class="size-4 transition group-hover:translate-x-0.5" />
          </span>
        </Link>
      </section>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRight, Banknote, ClipboardList, PackageCheck, Store, Users } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import PageHeader from '../../Components/PageHeader.vue'
import StatCard from '../../Components/StatCard.vue'
import { formatMoney, formatNumber } from '../../lib/format'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({ totalDealers: 0, activeSubscriptions: 0, totalCustomers: 0, totalRepairsCompleted: 0, totalRevenue: 0 }),
  },
})

const activeShare = computed(() => {
  const total = Number(props.stats.totalDealers) || 0
  return total ? Math.round((Number(props.stats.activeSubscriptions) / total) * 100) : 0
})

const quickLinks = [
  { href: '/admin/shops', icon: Store, title: 'Shops', text: 'Activate shops and manage plans and expiry dates.' },
  { href: '/admin/service-requests', icon: ClipboardList, title: 'Requests', text: 'Every repair request on the platform.' },
  { href: '/admin/customers', icon: Users, title: 'Customers', text: 'Registered customers and their activity.' },
]
</script>
