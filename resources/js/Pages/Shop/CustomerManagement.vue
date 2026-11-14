<template>
  <AppLayout title="Repair requests">
    <PageHeader title="Repair requests" subtitle="Accept new jobs, record your diagnosis and price, and keep customers updated." />

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
      <StatCard label="New requests" :value="counts.pending" :icon="Inbox" tone="amber" />
      <StatCard label="In progress" :value="counts.working" :icon="Wrench" tone="indigo" />
      <StatCard label="Completed" :value="counts.done" :icon="PackageCheck" tone="emerald" />
    </div>

    <div class="card overflow-hidden">
      <div class="flex flex-col gap-3 border-b border-slate-100 p-4 lg:flex-row lg:items-center lg:justify-between">
        <FilterTabs v-model="tab" :tabs="tabs" />
        <div class="lg:w-80">
          <SearchInput v-model="search" placeholder="Search name, phone or model…" />
        </div>
      </div>

      <!-- Desktop table -->
      <div v-if="filtered.length" class="hidden overflow-x-auto lg:block">
        <table class="data-table">
          <thead>
            <tr>
              <th>Customer</th>
              <th>Device</th>
              <th>Problem</th>
              <th>Cost</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="record in filtered" :key="record.service_id">
              <td>
                <div class="flex items-center gap-3">
                  <Avatar :name="record.customer_name" size="sm" />
                  <div class="min-w-0">
                    <p class="font-semibold text-slate-900">{{ record.customer_name }}</p>
                    <p class="text-xs text-slate-500">{{ record.contact }} · {{ formatDate(record.date) }}</p>
                  </div>
                </div>
              </td>
              <td>
                <p class="font-medium text-slate-800">{{ record.phone_model }}</p>
                <p class="font-mono text-xs text-slate-400">{{ record.phone_imei_number }}</p>
              </td>
              <td class="max-w-xs">
                <p class="line-clamp-2 text-slate-600">{{ record.customer_problem }}</p>
              </td>
              <td class="font-semibold whitespace-nowrap text-slate-900">{{ formatMoney(record.repair_cost) }}</td>
              <td><StatusBadge :status="record.status" /></td>
              <td>
                <div class="flex items-center justify-end gap-2">
                  <button
                    v-if="primaryAction(record)"
                    type="button"
                    class="btn btn-sm"
                    :class="primaryAction(record).className"
                    :disabled="busy"
                    @click="runPrimary(record)"
                  >
                    {{ primaryAction(record).label }}
                  </button>
                  <button type="button" class="btn btn-secondary btn-sm" @click="openManage(record)">Manage</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile cards -->
      <ul v-if="filtered.length" class="divide-y divide-slate-100 lg:hidden">
        <li v-for="record in filtered" :key="record.service_id" class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
              <Avatar :name="record.customer_name" size="sm" />
              <div class="min-w-0">
                <p class="truncate font-semibold text-slate-900">{{ record.customer_name }}</p>
                <p class="truncate text-xs text-slate-500">{{ record.phone_model }} · {{ record.contact }}</p>
              </div>
            </div>
            <StatusBadge :status="record.status" />
          </div>
          <p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ record.customer_problem }}</p>
          <div class="mt-3 flex items-center justify-between gap-2">
            <span class="text-sm font-semibold text-slate-900">{{ formatMoney(record.repair_cost, 'No quote') }}</span>
            <div class="flex gap-2">
              <button
                v-if="primaryAction(record)"
                type="button"
                class="btn btn-sm"
                :class="primaryAction(record).className"
                :disabled="busy"
                @click="runPrimary(record)"
              >
                {{ primaryAction(record).label }}
              </button>
              <button type="button" class="btn btn-secondary btn-sm" @click="openManage(record)">Manage</button>
            </div>
          </div>
        </li>
      </ul>

      <EmptyState
        v-if="!filtered.length"
        :icon="Inbox"
        :title="records.length ? 'No requests match' : 'No requests yet'"
        :description="records.length ? 'Try another filter or search.' : 'When customers send repair requests to your shop, they appear here.'"
      />
    </div>

    <!-- Manage panel -->
    <Modal
      :show="!!selected"
      size="lg"
      :title="selected ? `${selected.phone_model} · #${selected.service_id}` : ''"
      :subtitle="selected ? `${selected.customer_name} · ${selected.contact}` : ''"
      @close="selectedId = null"
    >
      <div v-if="selected" class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <StatusBadge :status="selected.status" />
          <span class="text-sm text-slate-500">Received {{ formatDate(selected.date) }}</span>
        </div>

        <RepairProgress :status="selected.status" />

        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70">
          <p class="text-xs font-semibold tracking-wide text-slate-400 uppercase">Customer's description</p>
          <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ selected.customer_problem }}</p>
          <p class="mt-3 text-xs text-slate-500">IMEI <span class="font-mono text-slate-700">{{ selected.phone_imei_number }}</span></p>
        </div>

        <div v-if="selected.editable" class="grid gap-4 sm:grid-cols-[1fr_200px]">
          <FormField id="shop_problem" label="Your diagnosis" :error="errors.shop_problem">
            <template #default="{ id, inputClass }">
              <textarea :id="id" v-model="draft.shop_problem" rows="3" :class="inputClass" placeholder="What did you find? e.g. Display connector damaged" />
            </template>
          </FormField>
          <FormField id="repair_cost" label="Repair cost (৳)" :error="errors.repair_cost">
            <template #default="{ id, inputClass }">
              <input :id="id" v-model="draft.repair_cost" type="number" min="0" step="0.01" inputmode="decimal" :class="inputClass" placeholder="0.00" />
            </template>
          </FormField>
        </div>
        <div v-else class="grid gap-4 sm:grid-cols-2">
          <div>
            <p class="label">Your diagnosis</p>
            <p class="text-sm whitespace-pre-line text-slate-700">{{ selected.shop_problem || 'Not provided' }}</p>
          </div>
          <div>
            <p class="label">Repair cost</p>
            <p class="text-sm font-semibold text-slate-900">{{ formatMoney(selected.repair_cost, 'Not set') }}</p>
          </div>
        </div>

        <div v-if="selected.status === 'in progress' && !readyToComplete" class="flex items-start gap-3 rounded-xl bg-amber-50 p-3.5 text-sm text-amber-800 ring-1 ring-amber-200">
          <Info class="mt-0.5 size-4 shrink-0" />
          Save a diagnosis and a repair cost before marking this job as completed.
        </div>
      </div>

      <template #footer>
        <button
          v-if="selected?.editable"
          type="button"
          class="btn btn-secondary"
          :disabled="busy || !detailsChanged"
          @click="saveDetails"
        >
          <Save class="size-4" />
          Save details
        </button>
        <button
          v-for="action in selected ? actionsFor(selected) : []"
          :key="action.key"
          type="button"
          class="btn"
          :class="action.className"
          :disabled="busy || (action.key === 'mark_complete' && !readyToComplete)"
          @click="runAction(selected, action.key)"
        >
          <component :is="action.icon" class="size-4" />
          {{ action.label }}
        </button>
      </template>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Check, Inbox, Info, PackageCheck, Play, Save, Truck, Wrench, X } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import Avatar from '../../Components/Avatar.vue'
import EmptyState from '../../Components/EmptyState.vue'
import FilterTabs from '../../Components/FilterTabs.vue'
import FormField from '../../Components/FormField.vue'
import Modal from '../../Components/Modal.vue'
import PageHeader from '../../Components/PageHeader.vue'
import RepairProgress from '../../Components/RepairProgress.vue'
import SearchInput from '../../Components/SearchInput.vue'
import StatCard from '../../Components/StatCard.vue'
import StatusBadge from '../../Components/StatusBadge.vue'
import { formatDate, formatMoney } from '../../lib/format'

const props = defineProps({
  records: { type: Array, default: () => [] },
})

const page = usePage()
const errors = computed(() => page.props.errors || {})

const search = ref('')
const tab = ref('all')
const busy = ref(false)
const selectedId = ref(null)
const draft = reactive({ shop_problem: '', repair_cost: '' })

const GROUPS = {
  new: ['pending'],
  working: ['accepted', 'in progress'],
  ready: ['completed', 'sent from shop'],
  closed: ['delivered', 'rejected'],
}

const counts = computed(() => ({
  pending: props.records.filter((r) => r.status === 'pending').length,
  working: props.records.filter((r) => GROUPS.working.includes(r.status)).length,
  done: props.records.filter((r) => ['completed', 'sent from shop', 'delivered'].includes(r.status)).length,
}))

const tabs = computed(() => [
  { value: 'all', label: 'All', count: props.records.length },
  { value: 'new', label: 'New', count: props.records.filter((r) => GROUPS.new.includes(r.status)).length },
  { value: 'working', label: 'Working', count: props.records.filter((r) => GROUPS.working.includes(r.status)).length },
  { value: 'ready', label: 'Ready / sent', count: props.records.filter((r) => GROUPS.ready.includes(r.status)).length },
  { value: 'closed', label: 'Closed', count: props.records.filter((r) => GROUPS.closed.includes(r.status)).length },
])

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  return props.records.filter((r) => {
    if (tab.value !== 'all' && !GROUPS[tab.value].includes(r.status)) return false
    if (!q) return true
    return [r.customer_name, r.contact, r.phone_model, r.phone_imei_number].some((v) => String(v || '').toLowerCase().includes(q))
  })
})

// Look the record up from props so the panel shows fresh data after every save.
const selected = computed(() => props.records.find((r) => r.service_id === selectedId.value) || null)

function hydrateDraft(record) {
  draft.shop_problem = record?.shop_problem || ''
  draft.repair_cost = record?.repair_cost === null || record?.repair_cost === undefined ? '' : String(record.repair_cost)
}

watch(selected, (record, previous) => {
  // Refill the inputs when opening a record, or when its saved values change.
  if (!record) return
  if (!previous || previous.service_id !== record.service_id
    || previous.shop_problem !== record.shop_problem || previous.repair_cost !== record.repair_cost) {
    hydrateDraft(record)
  }
})

function openManage(record) {
  selectedId.value = record.service_id
  hydrateDraft(record)
}

const problemChanged = computed(() => !!selected.value && draft.shop_problem.trim() !== (selected.value.shop_problem || ''))
const costChanged = computed(() => {
  if (!selected.value) return false
  const saved = selected.value.repair_cost === null ? '' : Number(selected.value.repair_cost)
  const current = String(draft.repair_cost).trim() === '' ? '' : Number(draft.repair_cost)
  return saved !== current
})
const detailsChanged = computed(() => problemChanged.value || costChanged.value)
const readyToComplete = computed(() => !!selected.value?.shop_problem && selected.value?.repair_cost !== null && !detailsChanged.value)

function post(url, data, onSuccess) {
  busy.value = true
  router.post(url, data, {
    preserveScroll: true,
    preserveState: true,
    onSuccess,
    onFinish: () => { busy.value = false },
  })
}

function saveDetails() {
  const record = selected.value
  const saveCost = () => {
    if (costChanged.value) post(`/shop/customers/${record.service_id}/cost`, { repair_cost: String(draft.repair_cost).trim() })
  }
  if (problemChanged.value) {
    post(`/shop/customers/${record.service_id}/problem`, { shop_problem: draft.shop_problem.trim() }, saveCost)
  } else {
    saveCost()
  }
}

function runAction(record, action) {
  post(`/shop/customers/${record.service_id}/status`, { action })
}

const ACTIONS = {
  pending: [
    { key: 'reject', label: 'Reject', className: 'btn-soft-danger', icon: X },
    { key: 'accept', label: 'Accept', className: 'btn-primary', icon: Check },
  ],
  accepted: [{ key: 'start_progress', label: 'Start repair', className: 'btn-primary', icon: Play }],
  'in progress': [{ key: 'mark_complete', label: 'Mark completed', className: 'btn-primary', icon: PackageCheck }],
  completed: [{ key: 'sent_from_shop', label: 'Mark as sent', className: 'btn-primary', icon: Truck }],
}

function actionsFor(record) {
  return ACTIONS[record.status] || []
}

/** One-click next step in the table. Completing needs details, so that opens the panel instead. */
function primaryAction(record) {
  if (record.status === 'pending') return { key: 'accept', label: 'Accept', className: 'btn-primary' }
  if (record.status === 'accepted') return { key: 'start_progress', label: 'Start repair', className: 'btn-soft' }
  if (record.status === 'completed') return { key: 'sent_from_shop', label: 'Mark as sent', className: 'btn-soft' }
  return null
}

function runPrimary(record) {
  runAction(record, primaryAction(record).key)
}
</script>
