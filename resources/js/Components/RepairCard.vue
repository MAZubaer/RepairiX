<template>
  <article class="card overflow-hidden" :class="needsAction ? 'ring-2 ring-brand-400' : ''">
    <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
      <div class="flex min-w-0 items-start gap-4">
        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-slate-900 text-white dark:bg-slate-800 dark:ring-1 dark:ring-slate-700">
          <Smartphone class="size-6" />
        </span>
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <h3 class="truncate text-lg font-bold text-slate-900 dark:text-slate-100">{{ record.phone_model }}</h3>
            <StatusBadge :status="record.status" />
          </div>
          <p class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1.5"><Store class="size-4" />{{ record.shop_name }}</span>
            <span class="inline-flex items-center gap-1.5"><Calendar class="size-4" />{{ formatDate(record.date) }}</span>
            <span class="text-slate-400 dark:text-slate-500">#{{ record.service_id }}</span>
          </p>
        </div>
      </div>
      <div class="shrink-0 text-left sm:text-right">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Repair cost</p>
        <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100">{{ formatMoney(record.repair_cost, 'Not quoted yet') }}</p>
      </div>
    </div>

    <div class="px-5 sm:px-6">
      <RepairProgress :status="record.status" />
      <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">{{ statusMessage }}</p>
    </div>

    <button
      type="button"
      class="mt-4 flex w-full items-center gap-1.5 px-5 pb-1 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 sm:px-6"
      :aria-expanded="showDetails"
      @click="showDetails = !showDetails"
    >
      <ChevronDown class="size-4 transition" :class="showDetails ? 'rotate-180' : ''" />
      {{ showDetails ? 'Hide details' : 'Show details' }}
    </button>

    <dl v-show="showDetails" class="mt-3 grid gap-px border-t border-slate-100 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 sm:grid-cols-2">
      <div class="bg-white dark:bg-slate-900 px-5 py-4 sm:px-6">
        <dt class="text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">Your description</dt>
        <dd class="mt-1 text-sm whitespace-pre-line text-slate-700 dark:text-slate-300">{{ record.customer_problem }}</dd>
      </div>
      <div class="bg-white dark:bg-slate-900 px-5 py-4 sm:px-6">
        <dt class="text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">Shop's diagnosis</dt>
        <dd class="mt-1 text-sm whitespace-pre-line" :class="record.shop_problem ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500'">
          {{ record.shop_problem || 'Not diagnosed yet' }}
        </dd>
      </div>
      <div class="bg-white dark:bg-slate-900 px-5 py-4 sm:px-6">
        <dt class="text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">Contact number</dt>
        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ record.contact || '—' }}</dd>
      </div>
      <div class="bg-white dark:bg-slate-900 px-5 py-4 sm:px-6">
        <dt class="text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">IMEI</dt>
        <dd class="mt-1 font-mono text-sm text-slate-700 dark:text-slate-300">{{ record.phone_imei_number || '—' }}</dd>
      </div>
    </dl>

    <div v-if="!showDetails" class="mt-4 border-t border-slate-100 dark:border-slate-800" />

    <!-- Actions -->
    <div v-if="actionable && record.status === 'sent from shop'" class="flex flex-col gap-3 border-t border-brand-100 dark:border-brand-500/20 bg-brand-50/60 dark:bg-brand-500/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
      <p class="text-sm font-medium text-brand-900 dark:text-brand-100">Got your phone back? Confirm delivery to finish the repair.</p>
      <button type="button" class="btn btn-primary" :disabled="busy" @click="$emit('accept', record)">
        <PackageCheck class="size-4" />
        Confirm delivery
      </button>
    </div>

    <div v-else-if="record.status === 'delivered'" class="flex flex-col gap-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/30 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
      <template v-if="actionable && (editing || !record.rating)">
        <div class="flex items-center gap-3">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ record.rating ? 'Update your rating' : 'How was the repair?' }}</p>
          <StarRating v-model="draft" interactive size="md" />
        </div>
        <div class="flex gap-2">
          <button v-if="record.rating" type="button" class="btn btn-ghost btn-sm" @click="cancelEdit">Cancel</button>
          <button type="button" class="btn btn-primary btn-sm" :disabled="!draft || busy" @click="submitRating">Submit rating</button>
        </div>
      </template>
      <template v-else>
        <div class="flex items-center gap-3">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ record.rating ? 'Your rating' : 'Not rated' }}</p>
          <StarRating v-if="record.rating" :model-value="Number(record.rating)" size="md" />
        </div>
        <button v-if="actionable" type="button" class="btn btn-ghost btn-sm" @click="editing = true">
          <Pencil class="size-4" />
          Change
        </button>
      </template>
    </div>
  </article>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Calendar, ChevronDown, PackageCheck, Pencil, Smartphone, Store } from 'lucide-vue-next'
import RepairProgress from './RepairProgress.vue'
import StarRating from './StarRating.vue'
import StatusBadge from './StatusBadge.vue'
import { formatDate, formatMoney } from '../lib/format'

const props = defineProps({
  record: { type: Object, required: true },
  actionable: { type: Boolean, default: true },
  busy: { type: Boolean, default: false },
})
const emit = defineEmits(['accept', 'rate'])

const editing = ref(false)
// Open repairs show their details; closed ones start collapsed to keep the list scannable.
const showDetails = ref(!['delivered', 'rejected'].includes(props.record.status))
const draft = ref(Number(props.record.rating) || 0)

watch(() => props.record.rating, (r) => {
  draft.value = Number(r) || 0
  editing.value = false
})

const needsAction = computed(() => props.actionable && (props.record.status === 'sent from shop' || (props.record.status === 'delivered' && !props.record.rating)))

const statusMessage = computed(() => {
  const shop = props.record.shop_name || 'The shop'
  return {
    pending: `${shop} has received your request and will review it shortly.`,
    accepted: `${shop} accepted your request and will start the repair soon.`,
    rejected: 'You can send a request to another shop.',
    'in progress': `${shop} is repairing your phone right now.`,
    completed: 'The repair is finished. The shop will send your phone back soon.',
    'sent from shop': 'Your phone is on its way back to you.',
    delivered: props.record.rating ? 'Delivered and rated. Thanks for your feedback!' : 'Delivered. Please rate your experience.',
  }[props.record.status] || ''
})

function cancelEdit() {
  editing.value = false
  draft.value = Number(props.record.rating) || 0
}

function submitRating() {
  emit('rate', props.record, draft.value)
}
</script>
