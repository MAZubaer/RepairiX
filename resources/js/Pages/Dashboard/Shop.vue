<template>
  <AppLayout title="Shop overview">
    <ShopProfileHeader :shop="shop" :logo-url="logo?.url">
      <template #actions>
        <Link href="/shop/edit" class="btn btn-secondary">
          <Pencil class="size-4" />
          Edit profile
        </Link>
        <Link href="/shop/customers" class="btn btn-primary">
          <ClipboardList class="size-4" />
          Manage requests
        </Link>
      </template>
    </ShopProfileHeader>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <StatCard label="New requests" :value="stats.pending" :icon="Inbox" tone="amber" hint="Waiting for you to accept" />
      <StatCard label="Active jobs" :value="stats.active" :icon="Wrench" tone="indigo" hint="Accepted, repairing or ready" />
      <StatCard label="Finished" :value="stats.finished" :icon="PackageCheck" tone="emerald" hint="Sent back or delivered" />
      <StatCard label="Repair value" :value="formatMoney(stats.earned, '৳0')" :icon="Banknote" tone="brand" hint="From completed jobs" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
      <section class="card overflow-hidden lg:col-span-2">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 px-6 py-4">
          <h2 class="font-bold text-slate-900 dark:text-slate-100">Latest requests</h2>
          <Link href="/shop/customers" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 dark:text-brand-300 hover:text-brand-800 dark:hover:text-brand-200">
            View all
            <ArrowRight class="size-4" />
          </Link>
        </div>
        <ul v-if="recentRequests.length" class="divide-y divide-slate-100 dark:divide-slate-800">
          <li v-for="r in recentRequests" :key="r.service_id">
            <Link href="/shop/customers" class="flex items-center gap-4 px-6 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
              <Avatar :name="r.customer_name" />
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-200">{{ r.customer_name }}</p>
                <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ deviceMeta(r.device_type).label }} · {{ r.phone_model }} · {{ formatDate(r.date) }}</p>
              </div>
              <StatusBadge :status="r.status" />
            </Link>
          </li>
        </ul>
        <EmptyState v-else :icon="Inbox" title="No requests yet" description="New repair requests from customers will show up here." />
      </section>

      <div class="space-y-6">
        <section class="card p-6">
          <div class="flex items-center justify-between">
            <h2 class="font-bold text-slate-900 dark:text-slate-100">Subscription</h2>
            <span class="chip bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-600/20 dark:ring-emerald-400/25 ring-inset">
              <span class="size-1.5 rounded-full bg-emerald-500" />
              Active
            </span>
          </div>
          <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Plan</p>
          <p class="font-semibold text-slate-900 dark:text-slate-100 capitalize">{{ subscription.plan || 'Custom' }}</p>
          <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Renews / expires</p>
          <p class="font-semibold text-slate-900 dark:text-slate-100">{{ formatDate(subscription.expiry_date) }}</p>
          <Link href="/shop/pricing" class="btn btn-secondary mt-5 w-full">Manage billing</Link>
        </section>

        <section class="card p-6">
          <h2 class="font-bold text-slate-900 dark:text-slate-100">Services</h2>
          <p v-if="shop.services_provided" class="mt-3 line-clamp-6 text-sm leading-relaxed whitespace-pre-line text-slate-600 dark:text-slate-400">{{ shop.services_provided }}</p>
          <p v-else class="mt-3 text-sm text-slate-400 dark:text-slate-500">Tell customers what you repair. It helps them choose you.</p>
          <Link v-if="!shop.services_provided" href="/shop/edit" class="btn btn-soft btn-sm mt-4">Add services</Link>
        </section>
      </div>
    </div>

    <section class="card mt-6 p-6 sm:p-8">
      <div class="mb-5 flex items-center justify-between">
        <h2 class="font-bold text-slate-900 dark:text-slate-100">Shop photos</h2>
        <Link href="/shop/photos" class="text-sm font-semibold text-brand-700 dark:text-brand-300 hover:text-brand-800 dark:hover:text-brand-200">Manage photos</Link>
      </div>
      <ShopGallery :images="visibleGallery" empty-text="Add photos of your shop and work so customers can see what to expect." />
    </section>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRight, Banknote, ClipboardList, Inbox, PackageCheck, Pencil, Wrench } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import Avatar from '../../Components/Avatar.vue'
import EmptyState from '../../Components/EmptyState.vue'
import ShopGallery from '../../Components/ShopGallery.vue'
import ShopProfileHeader from '../../Components/ShopProfileHeader.vue'
import StatCard from '../../Components/StatCard.vue'
import StatusBadge from '../../Components/StatusBadge.vue'
import { formatDate, formatMoney } from '../../lib/format'
import { deviceMeta } from '../../lib/devices'

const props = defineProps({
  shop: { type: Object, required: true },
  logo: { type: Object, default: null },
  gallery: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({ pending: 0, active: 0, finished: 0, earned: 0 }) },
  subscription: { type: Object, default: () => ({ plan: null, expiry_date: null }) },
  recentRequests: { type: Array, default: () => [] },
})

const visibleGallery = computed(() => (Array.isArray(props.gallery) ? props.gallery : []).filter((i) => i && i.url))
</script>
