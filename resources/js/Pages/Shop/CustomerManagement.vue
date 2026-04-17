<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <ShopNavbar />

    <main class="flex-1 py-10">
      <div class="w-full max-w-6xl mx-auto px-6">
        <section class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <h1 class="text-5xl font-extrabold leading-tight">Customer Management</h1>

          <div class="w-full lg:w-[360px]">
            <div class="relative">
              <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 014.314 8.91l3.638 3.638a1 1 0 01-1.414 1.414l-3.638-3.638A5.5 5.5 0 118.5 3zm0 2a3.5 3.5 0 100 7 3.5 3.5 0 000-7z" clip-rule="evenodd" />
                </svg>
              </span>
              <input
                v-model="searchPhone"
                type="text"
                class="form-input search-input"
                placeholder="Search customers by phone number..."
              />
            </div>
          </div>
        </section>

        <section class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
          <article class="summary-box">
            <div class="text-sm text-gray-600">Pending Requests</div>
            <div class="mt-2 flex items-center justify-between">
              <div class="text-4xl font-bold">{{ pendingCount }}</div>
              <span class="summary-icon pending">◷</span>
            </div>
          </article>

          <article class="summary-box">
            <div class="text-sm text-gray-600">In Progress</div>
            <div class="mt-2 flex items-center justify-between">
              <div class="text-4xl font-bold">{{ inProgressCount }}</div>
              <span class="summary-icon progress">◉</span>
            </div>
          </article>

          <article class="summary-box">
            <div class="text-sm text-gray-600">Completed</div>
            <div class="mt-2 flex items-center justify-between">
              <div class="text-4xl font-bold">{{ completedCount }}</div>
              <span class="summary-icon complete">✓</span>
            </div>
          </article>
        </section>

        <section class="mt-6 rounded-xl border border-slate-300 bg-white overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px] text-left">
              <thead class="bg-slate-50 border-b border-slate-300">
                <tr class="text-sm text-slate-700">
                  <th class="px-4 py-4 font-semibold">Customer</th>
                  <th class="px-4 py-4 font-semibold">Phone Model</th>
                  <th class="px-4 py-4 font-semibold">Contact</th>
                  <th class="px-4 py-4 font-semibold">Customer Problem</th>
                  <th class="px-4 py-4 font-semibold">Identified Problem</th>
                  <th class="px-4 py-4 font-semibold">Cost</th>
                  <th class="px-4 py-4 font-semibold">Date</th>
                  <th class="px-4 py-4 font-semibold">Status</th>
                  <th class="px-4 py-4 font-semibold">Actions</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="record in filteredRecords"
                  :key="record.service_id"
                  class="border-b border-slate-200 align-top"
                >
                  <td class="px-4 py-4">
                    <div class="font-bold text-2xl leading-tight">{{ record.customer_name }}</div>
                    <div class="text-sm text-slate-500">IMEI: {{ record.phone_imei_number }}</div>
                  </td>

                  <td class="px-4 py-4 text-lg">{{ record.phone_model }}</td>
                  <td class="px-4 py-4 text-lg">{{ record.contact }}</td>
                  <td class="px-4 py-4 text-base whitespace-pre-line">{{ record.customer_problem }}</td>

                  <td class="px-4 py-4 w-[230px]">
                    <textarea
                      v-model="problemDrafts[record.service_id]"
                      rows="2"
                      class="form-input"
                      placeholder="Enter identified problem..."
                    ></textarea>
                    <button
                      type="button"
                      class="btn-update mt-2 w-full"
                      @click="submitProblem(record)"
                    >
                      Update
                    </button>
                  </td>

                  <td class="px-4 py-4 w-[210px]">
                    <input
                      v-model="costDrafts[record.service_id]"
                      type="text"
                      class="form-input"
                      placeholder="Enter cost..."
                    />
                    <button
                      type="button"
                      class="btn-update mt-2 w-full"
                      @click="submitCost(record)"
                    >
                      Update
                    </button>
                  </td>

                  <td class="px-4 py-4 text-lg">{{ record.date }}</td>

                  <td class="px-4 py-4">
                    <span :class="statusClass(record.status)">{{ statusLabel(record.status) }}</span>
                  </td>

                  <td class="px-4 py-4">
                    <div class="flex flex-wrap items-center gap-2">
                      <button
                        v-for="action in actionsForRecord(record)"
                        :key="action.key"
                        type="button"
                        :class="action.className"
                        @click="submitStatusAction(record, action.key)"
                      >
                        {{ action.label }}
                      </button>

                      <span v-if="!actionsForRecord(record).length" class="text-slate-500 text-sm">No actions</span>
                    </div>
                  </td>
                </tr>

                <tr v-if="!filteredRecords.length">
                  <td colspan="9" class="px-4 py-10 text-center text-slate-500">
                    No requests found for this phone number search.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <p v-if="errors.status" class="mt-3 text-red-600 text-sm">{{ errors.status[0] }}</p>
      </div>
    </main>

    <footer class="h-12"></footer>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { usePage } from '@inertiajs/inertia-vue3'
import ShopNavbar from '../../Components/ShopNavbar.vue'

const props = defineProps({
  records: { type: Array, default: () => [] },
})

const page = usePage()
const errors = computed(() => page.props.value.errors || {})

const searchPhone = ref('')
const problemDrafts = reactive({})
const costDrafts = reactive({})

function hydrateDrafts(records) {
  Object.keys(problemDrafts).forEach((k) => delete problemDrafts[k])
  Object.keys(costDrafts).forEach((k) => delete costDrafts[k])

  ;(records || []).forEach((record) => {
    problemDrafts[record.service_id] = record.shop_problem || ''
    costDrafts[record.service_id] =
      record.repair_cost === null || record.repair_cost === undefined ? '' : String(record.repair_cost)
  })
}

watch(
  () => props.records,
  (records) => hydrateDrafts(records),
  { immediate: true }
)

const filteredRecords = computed(() => {
  const q = searchPhone.value.trim()
  if (!q) return props.records
  return props.records.filter((record) => String(record.contact || '').includes(q))
})

const pendingCount = computed(() => props.records.filter((r) => r.status === 'pending').length)
const inProgressCount = computed(() => props.records.filter((r) => r.status === 'in progress').length)
const completedCount = computed(() =>
  props.records.filter((r) => ['completed', 'sent from shop', 'delivered'].includes(r.status)).length
)

function submitProblem(record) {
  Inertia.post(
    `/shop/customers/${record.service_id}/problem`,
    { shop_problem: (problemDrafts[record.service_id] || '').trim() },
    { preserveScroll: true }
  )
}

function submitCost(record) {
  Inertia.post(
    `/shop/customers/${record.service_id}/cost`,
    { repair_cost: String(costDrafts[record.service_id] || '').trim() },
    { preserveScroll: true }
  )
}

function submitStatusAction(record, action) {
  Inertia.post(
    `/shop/customers/${record.service_id}/status`,
    { action },
    { preserveScroll: true }
  )
}

function actionsForRecord(record) {
  if (record.status === 'pending') {
    return [
      { key: 'accept', label: 'Accept', className: 'btn-action btn-accept' },
      { key: 'reject', label: 'Reject', className: 'btn-action btn-reject' },
    ]
  }

  if (record.status === 'accepted') {
    return [{ key: 'start_progress', label: 'Start Progress', className: 'btn-action btn-progress' }]
  }

  if (record.status === 'in progress') {
    return [{ key: 'mark_complete', label: 'Mark Complete', className: 'btn-action btn-complete' }]
  }

  if (record.status === 'completed') {
    return [{ key: 'sent_from_shop', label: 'Send from Shop', className: 'btn-action btn-send' }]
  }

  return []
}

function statusLabel(status) {
  const map = {
    pending: 'Pending',
    accepted: 'Accepted',
    rejected: 'Rejected',
    'in progress': 'In Progress',
    completed: 'Completed',
    'sent from shop': 'Sent from Shop',
    delivered: 'Delivered',
  }

  return map[status] || status
}

function statusClass(status) {
  const base = 'inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold'

  const classes = {
    pending: 'bg-amber-100 text-amber-700',
    accepted: 'bg-green-100 text-green-700',
    rejected: 'bg-rose-100 text-rose-700',
    'in progress': 'bg-blue-100 text-blue-700',
    completed: 'bg-purple-100 text-purple-700',
    'sent from shop': 'bg-teal-100 text-teal-700',
    delivered: 'bg-slate-200 text-slate-700',
  }

  return `${base} ${classes[status] || 'bg-slate-100 text-slate-700'}`
}
</script>

<style scoped>
.form-input {
  width: 100%;
  min-height: 2.8rem;
  padding: 0.65rem 0.9rem;
  border: 1px solid rgba(15, 23, 42, 0.16);
  border-radius: 0.7rem;
  background: #fff;
}
.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.08);
}
.search-input {
  padding-left: 2.8rem;
}
.summary-box {
  border: 1px solid rgba(15, 23, 42, 0.14);
  border-radius: 0.75rem;
  padding: 1rem 1.1rem;
  background: #fff;
}
.summary-icon {
  width: 2.6rem;
  height: 2.6rem;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.1rem;
}
.summary-icon.pending {
  color: #a16207;
  background: #fef3c7;
}
.summary-icon.progress {
  color: #1d4ed8;
  background: #dbeafe;
}
.summary-icon.complete {
  color: #16a34a;
  background: #dcfce7;
}
.btn-update {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.55rem 0.9rem;
  border-radius: 0.65rem;
  background: var(--color-primary);
  color: #fff;
  font-weight: 700;
}
.btn-update:hover {
  opacity: 0.92;
}
.btn-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.65rem;
  padding: 0.48rem 0.9rem;
  color: #fff;
  font-size: 0.93rem;
  font-weight: 700;
}
.btn-accept {
  background: #22c55e;
}
.btn-reject {
  background: #ef4444;
}
.btn-progress {
  background: #3b82f6;
}
.btn-complete {
  background: #9333ea;
}
.btn-send {
  background: #14b8a6;
}
</style>
