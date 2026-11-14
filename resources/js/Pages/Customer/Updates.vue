<template>
  <AppLayout title="My repairs">
    <PageHeader title="My repairs" subtitle="Follow every request from the moment you send it until your phone is back.">
      <template #actions>
        <Link href="/dashboard/customer" class="btn btn-primary">
          <Plus class="size-4" />
          New request
        </Link>
      </template>
    </PageHeader>

    <div class="mb-6">
      <FilterTabs v-model="tab" :tabs="tabs" />
    </div>

    <div v-if="visible.length" class="space-y-5">
      <RepairCard
        v-for="record in visible"
        :key="record.service_id"
        :record="record"
        :busy="busyId === record.service_id"
        @accept="acceptDelivery"
        @rate="submitRating"
      />
    </div>

    <div v-else class="card">
      <EmptyState
        :icon="Wrench"
        :title="records.length ? 'Nothing in this view' : 'No repair requests yet'"
        :description="records.length ? 'Try another filter.' : 'Find a shop and send your first request. It only takes a minute.'"
      >
        <Link v-if="!records.length" href="/dashboard/customer" class="btn btn-primary">Find a shop</Link>
      </EmptyState>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Plus, Wrench } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import EmptyState from '../../Components/EmptyState.vue'
import FilterTabs from '../../Components/FilterTabs.vue'
import PageHeader from '../../Components/PageHeader.vue'
import RepairCard from '../../Components/RepairCard.vue'
import { ACTIVE_STATUSES, DONE_STATUSES } from '../../lib/status'

const props = defineProps({
  records: { type: Array, default: () => [] },
})

const busyId = ref(null)

const needsAction = (r) => r.status === 'sent from shop' || (r.status === 'delivered' && !r.rating)

const groups = computed(() => ({
  all: props.records,
  action: props.records.filter(needsAction),
  active: props.records.filter((r) => ACTIVE_STATUSES.includes(r.status)),
  done: props.records.filter((r) => DONE_STATUSES.includes(r.status)),
}))

// Open on "Needs action" when something is waiting for the customer.
const tab = ref(groups.value.action.length ? 'action' : 'all')

const tabs = computed(() => [
  { value: 'all', label: 'All', count: groups.value.all.length },
  { value: 'action', label: 'Needs action', count: groups.value.action.length },
  { value: 'active', label: 'In progress', count: groups.value.active.length },
  { value: 'done', label: 'Closed', count: groups.value.done.length },
])

const visible = computed(() => groups.value[tab.value] || [])

function acceptDelivery(record) {
  busyId.value = record.service_id
  router.post(`/customer/updates/${record.service_id}/accept`, {}, {
    preserveScroll: true,
    onFinish: () => { busyId.value = null },
  })
}

function submitRating(record, rating) {
  busyId.value = record.service_id
  router.post(`/customer/updates/${record.service_id}/rating`, { rating }, {
    preserveScroll: true,
    onFinish: () => { busyId.value = null },
  })
}
</script>
