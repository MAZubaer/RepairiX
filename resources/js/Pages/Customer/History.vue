<template>
  <AppLayout title="Repair history">
    <PageHeader title="Repair history" subtitle="Your completed and delivered repairs." />

    <div v-if="records.length" class="mb-6 grid gap-4 sm:grid-cols-3">
      <StatCard label="Repairs finished" :value="records.length" :icon="PackageCheck" />
      <StatCard label="Total spent" :value="formatMoney(totalSpent, '৳0')" :icon="Banknote" tone="emerald" />
      <StatCard label="Average rating you gave" :value="averageRating" :icon="Star" tone="amber" />
    </div>

    <div v-if="records.length" class="space-y-5">
      <RepairCard v-for="record in records" :key="record.service_id" :record="record" :actionable="false" />
    </div>

    <div v-else class="card">
      <EmptyState :icon="History" title="No finished repairs yet" description="Completed and delivered repairs will appear here.">
        <Link href="/update" class="btn btn-secondary">View active repairs</Link>
      </EmptyState>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Banknote, History, PackageCheck, Star } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import EmptyState from '../../Components/EmptyState.vue'
import PageHeader from '../../Components/PageHeader.vue'
import RepairCard from '../../Components/RepairCard.vue'
import StatCard from '../../Components/StatCard.vue'
import { formatMoney } from '../../lib/format'

const props = defineProps({
  records: { type: Array, default: () => [] },
})

const totalSpent = computed(() => props.records.reduce((sum, r) => sum + (Number(r.repair_cost) || 0), 0))

const averageRating = computed(() => {
  const rated = props.records.filter((r) => Number(r.rating) > 0)
  if (!rated.length) return '—'
  return (rated.reduce((s, r) => s + Number(r.rating), 0) / rated.length).toFixed(1)
})
</script>
