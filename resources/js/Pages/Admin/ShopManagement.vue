<template>
  <AppLayout title="Shops">
    <PageHeader eyebrow="Admin" title="Shops" subtitle="Manage subscriptions, plans and activation for every repair shop." />

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
      <StatCard label="Total shops" :value="summary.total" :icon="Store" />
      <StatCard label="Active" :value="summary.active" :icon="BadgeCheck" tone="emerald" />
      <StatCard label="Inactive" :value="summary.inactive" :icon="CircleOff" tone="rose" />
    </div>

    <div class="card overflow-hidden">
      <div class="flex flex-col gap-3 border-b border-slate-100 dark:border-slate-800 p-4 lg:flex-row lg:items-center lg:justify-between">
        <FilterTabs v-model="tab" :tabs="tabs" />
        <div class="lg:w-80">
          <SearchInput v-model="search" placeholder="Search name, email or location…" />
        </div>
      </div>

      <div v-if="filteredShops.length" class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Shop</th>
              <th>Contact</th>
              <th>Status</th>
              <th>Plan</th>
              <th>Expires</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="shop in filteredShops" :key="shop.shop_id">
              <td>
                <div class="flex items-center gap-3">
                  <Avatar :name="shop.name" rounded />
                  <div class="min-w-0">
                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ shop.name }}</p>
                    <p class="flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400"><MapPin class="size-3" />{{ shop.location }}</p>
                  </div>
                </div>
              </td>
              <td>
                <p class="text-slate-700 dark:text-slate-300">{{ shop.contact_email }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ shop.contact_phone }}</p>
              </td>
              <td>
                <span
                  class="chip ring-1 ring-inset"
                  :class="shop.status === 'activated' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 ring-emerald-600/20 dark:ring-emerald-400/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 ring-slate-500/20 dark:ring-slate-400/20'"
                >
                  <span class="size-1.5 rounded-full" :class="shop.status === 'activated' ? 'bg-emerald-500' : 'bg-slate-400'" />
                  {{ shop.status === 'activated' ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>
                <select class="input w-32 py-2" :value="shop.plan" aria-label="Plan" @change="changePlan(shop, $event.target.value)">
                  <option value="monthly">Monthly</option>
                  <option value="yearly">Yearly</option>
                </select>
              </td>
              <td>
                <input class="input w-40 py-2" type="date" :value="shop.expiry_date" aria-label="Expiry date" @change="changeExpiry(shop, $event.target.value)" />
              </td>
              <td class="text-right">
                <button
                  type="button"
                  class="btn btn-sm"
                  :class="shop.status === 'activated' ? 'btn-soft-danger' : 'btn-primary'"
                  @click="toggleStatus(shop)"
                >
                  <Power class="size-3.5" />
                  {{ shop.status === 'activated' ? 'Deactivate' : 'Activate' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <EmptyState v-else :icon="Store" title="No shops found" description="Try another search or filter." />
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { BadgeCheck, CircleOff, MapPin, Power, Store } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import Avatar from '../../Components/Avatar.vue'
import EmptyState from '../../Components/EmptyState.vue'
import FilterTabs from '../../Components/FilterTabs.vue'
import PageHeader from '../../Components/PageHeader.vue'
import SearchInput from '../../Components/SearchInput.vue'
import StatCard from '../../Components/StatCard.vue'

const props = defineProps({
  shops: { type: Array, default: () => [] },
  summary: { type: Object, default: () => ({ total: 0, active: 0, inactive: 0 }) },
})

const search = ref('')
const tab = ref('all')

const tabs = computed(() => [
  { value: 'all', label: 'All', count: props.shops.length },
  { value: 'activated', label: 'Active', count: props.shops.filter((s) => s.status === 'activated').length },
  { value: 'not_activated', label: 'Inactive', count: props.shops.filter((s) => s.status !== 'activated').length },
])

const filteredShops = computed(() => {
  const q = search.value.trim().toLowerCase()
  return props.shops.filter((shop) => {
    if (tab.value === 'activated' && shop.status !== 'activated') return false
    if (tab.value === 'not_activated' && shop.status === 'activated') return false
    return !q || [shop.name, shop.owner, shop.location, shop.contact_email].some((v) => String(v || '').toLowerCase().includes(q))
  })
})

const opts = { preserveScroll: true, preserveState: true }

function toggleStatus(shop) {
  router.post(`/admin/shops/${shop.shop_id}/toggle-status`, {}, opts)
}

function changePlan(shop, plan) {
  router.post(`/admin/shops/${shop.shop_id}/plan`, { plan }, opts)
}

function changeExpiry(shop, expiryDate) {
  if (!expiryDate) return
  router.post(`/admin/shops/${shop.shop_id}/expiry`, { expiry_date: expiryDate }, opts)
}
</script>
