<template>
  <AppLayout title="All requests">
    <PageHeader eyebrow="Admin" title="Service requests" subtitle="Every repair request across the platform." />

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
      <button
        v-for="item in summaryItems"
        :key="item.key"
        type="button"
        class="card flex items-center justify-between gap-3 p-4 text-left transition hover:ring-brand-300"
        :class="statusFilter === item.status ? 'ring-2 ring-brand-500' : ''"
        @click="statusFilter = statusFilter === item.status ? 'all' : item.status"
      >
        <div>
          <p class="text-xs font-medium text-slate-500">{{ item.label }}</p>
          <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ summary[item.key] ?? 0 }}</p>
        </div>
        <span class="size-2.5 rounded-full" :class="item.dot" />
      </button>
    </div>

    <div class="card overflow-hidden">
      <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500">
          Showing <span class="font-semibold text-slate-800">{{ filteredRequests.length }}</span> of {{ requests.length }}
          <button v-if="statusFilter !== 'all'" type="button" class="ml-2 font-semibold text-brand-700 hover:underline" @click="statusFilter = 'all'">
            Clear filter
          </button>
        </p>
        <div class="sm:w-80">
          <SearchInput v-model="search" placeholder="Search customer, shop or model…" />
        </div>
      </div>

      <div v-if="filteredRequests.length" class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Request</th>
              <th>Customer</th>
              <th>Shop</th>
              <th>Problem</th>
              <th>Diagnosis</th>
              <th>Cost</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="record in filteredRequests" :key="record.service_id">
              <td>
                <p class="font-semibold text-slate-900">{{ record.phone_model }}</p>
                <p class="text-xs text-slate-500">#{{ record.service_id }} · {{ formatDate(record.date) }}</p>
              </td>
              <td>
                <p class="font-medium text-slate-800">{{ record.customer_name }}</p>
                <p class="text-xs text-slate-500">{{ record.customer_email }}</p>
                <p class="text-xs text-slate-500">{{ record.customer_phone }}</p>
              </td>
              <td class="font-medium text-slate-800">{{ record.shop_name }}</td>
              <td class="max-w-[220px]"><p class="line-clamp-2 text-slate-600">{{ record.customer_problem }}</p></td>
              <td class="max-w-[220px]">
                <p class="line-clamp-2" :class="record.shop_problem ? 'text-slate-600' : 'text-slate-400'">{{ record.shop_problem || 'Not yet' }}</p>
              </td>
              <td class="font-semibold whitespace-nowrap text-slate-900">{{ formatMoney(record.cost) }}</td>
              <td><StatusBadge :status="record.status" /></td>
            </tr>
          </tbody>
        </table>
      </div>
      <EmptyState v-else :icon="ClipboardList" title="No requests found" description="Try another search or status." />
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { ClipboardList } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import EmptyState from '../../Components/EmptyState.vue'
import PageHeader from '../../Components/PageHeader.vue'
import SearchInput from '../../Components/SearchInput.vue'
import StatusBadge from '../../Components/StatusBadge.vue'
import { formatDate, formatMoney } from '../../lib/format'
import { STATUS } from '../../lib/status'

const props = defineProps({
  requests: { type: Array, default: () => [] },
  summary: {
    type: Object,
    default: () => ({
      total: 0,
      pending: 0,
      accepted: 0,
      rejected: 0,
      in_progress: 0,
      completed: 0,
      sent_from_shop: 0,
      delivered: 0,
    }),
  },
})

const search = ref('')
const statusFilter = ref('all')

const summaryItems = [
  { key: 'total', label: 'Total requests', status: 'all', dot: 'bg-slate-400' },
  ...[
    ['pending', 'pending'],
    ['accepted', 'accepted'],
    ['rejected', 'rejected'],
    ['in_progress', 'in progress'],
    ['completed', 'completed'],
    ['sent_from_shop', 'sent from shop'],
    ['delivered', 'delivered'],
  ].map(([key, status]) => ({ key, status, label: STATUS[status].label, dot: STATUS[status].dot })),
]

const filteredRequests = computed(() => {
  const q = search.value.trim().toLowerCase()
  return props.requests.filter((record) => {
    if (statusFilter.value !== 'all' && record.status !== statusFilter.value) return false
    return !q || [record.customer_name, record.customer_email, record.shop_name, record.phone_model, record.status]
      .some((v) => String(v || '').toLowerCase().includes(q))
  })
})
</script>
