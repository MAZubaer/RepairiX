<template>
  <AppLayout title="Customers">
    <PageHeader eyebrow="Admin" title="Customers" subtitle="Everyone registered as a customer, with their repair activity." />

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <StatCard label="Customers" :value="summary.total_customers" :icon="Users" tone="sky" />
      <StatCard label="Total requests" :value="summary.total_requests" :icon="ClipboardList" />
      <StatCard label="Active requests" :value="summary.active_requests" :icon="Wrench" tone="amber" />
      <StatCard label="Completed requests" :value="summary.completed_requests" :icon="PackageCheck" tone="emerald" />
    </div>

    <div class="card overflow-hidden">
      <div class="flex flex-col gap-3 border-b border-slate-100 dark:border-slate-800 p-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">
          <span class="font-semibold text-slate-800 dark:text-slate-200">{{ filteredCustomers.length }}</span> customers
        </p>
        <div class="sm:w-80">
          <SearchInput v-model="search" placeholder="Search name, email, phone or address…" />
        </div>
      </div>

      <div v-if="filteredCustomers.length" class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Customer</th>
              <th>Contact</th>
              <th>Address</th>
              <th>Joined</th>
              <th>Requests</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="customer in filteredCustomers" :key="customer.customer_id">
              <td>
                <div class="flex items-center gap-3">
                  <Avatar :name="customer.name" />
                  <div>
                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ customer.name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">#C{{ String(customer.customer_id).padStart(4, '0') }}</p>
                  </div>
                </div>
              </td>
              <td>
                <p class="text-slate-700 dark:text-slate-300">{{ customer.email }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ customer.phone }}</p>
              </td>
              <td class="text-slate-600 dark:text-slate-400">{{ customer.address }}</td>
              <td class="whitespace-nowrap text-slate-600 dark:text-slate-400">{{ formatDate(customer.join_date) }}</td>
              <td>
                <div class="flex items-center gap-1.5 text-xs font-semibold">
                  <span class="chip bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" title="Total">{{ customer.total_requests }} total</span>
                  <span v-if="customer.active_requests" class="chip bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300" title="Active">{{ customer.active_requests }} active</span>
                  <span v-if="customer.completed_requests" class="chip bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300" title="Completed">{{ customer.completed_requests }} done</span>
                </div>
              </td>
              <td class="text-right">
                <button type="button" class="btn btn-soft-danger btn-sm" @click="pendingDelete = customer">
                  <Trash2 class="size-3.5" />
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <EmptyState v-else :icon="Users" title="No customers found" description="Try another search." />
    </div>

    <ConfirmDialog
      :show="!!pendingDelete"
      danger
      :title="`Delete ${pendingDelete?.name || 'customer'}?`"
      message="This permanently removes the customer account and all of their repair requests."
      confirm-label="Delete customer"
      :processing="deleting"
      @cancel="pendingDelete = null"
      @confirm="deleteCustomer"
    />
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { ClipboardList, PackageCheck, Trash2, Users, Wrench } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import Avatar from '../../Components/Avatar.vue'
import ConfirmDialog from '../../Components/ConfirmDialog.vue'
import EmptyState from '../../Components/EmptyState.vue'
import PageHeader from '../../Components/PageHeader.vue'
import SearchInput from '../../Components/SearchInput.vue'
import StatCard from '../../Components/StatCard.vue'
import { formatDate } from '../../lib/format'

const props = defineProps({
  customers: { type: Array, default: () => [] },
  summary: {
    type: Object,
    default: () => ({ total_customers: 0, total_requests: 0, active_requests: 0, completed_requests: 0 }),
  },
})

const search = ref('')
const pendingDelete = ref(null)
const deleting = ref(false)

const filteredCustomers = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return props.customers
  return props.customers.filter((c) => [c.name, c.email, c.phone, c.address].some((v) => String(v || '').toLowerCase().includes(q)))
})

function deleteCustomer() {
  if (!pendingDelete.value) return
  deleting.value = true
  router.delete(`/admin/customers/${pendingDelete.value.customer_id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false
      pendingDelete.value = null
    },
  })
}
</script>
