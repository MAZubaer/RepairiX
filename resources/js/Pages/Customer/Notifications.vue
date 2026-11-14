<template>
  <AppLayout title="Notifications">
    <div class="mx-auto max-w-3xl">
      <PageHeader title="Notifications" subtitle="Status updates from the shops working on your repairs." />

      <div v-if="notifications.length" class="card divide-y divide-slate-100">
        <div v-for="note in notifications" :key="note.id" class="flex gap-4 p-5 transition hover:bg-slate-50/70">
          <span class="grid size-10 shrink-0 place-items-center rounded-full" :class="toneFor(note.message).wrap">
            <component :is="toneFor(note.message).icon" class="size-5" />
          </span>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-slate-800">{{ note.message }}</p>
            <p class="mt-1 text-xs text-slate-500" :title="note.time">{{ note.relative_time || note.time }}</p>
          </div>
        </div>
      </div>

      <div v-else class="card">
        <EmptyState :icon="BellOff" title="You're all caught up" description="When a shop updates one of your repairs, you'll see it here." />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Bell, BellOff, CircleCheck, CircleX, PackageCheck, Truck, Wrench } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import EmptyState from '../../Components/EmptyState.vue'
import PageHeader from '../../Components/PageHeader.vue'

defineProps({
  notifications: { type: Array, default: () => [] },
})

// Messages come from the server as text, so pick an icon from their wording.
function toneFor(message) {
  const m = String(message).toLowerCase()
  if (m.includes('rejected')) return { icon: CircleX, wrap: 'bg-rose-50 text-rose-600' }
  if (m.includes('sent from shop')) return { icon: Truck, wrap: 'bg-teal-50 text-teal-600' }
  if (m.includes('completed')) return { icon: PackageCheck, wrap: 'bg-violet-50 text-violet-600' }
  if (m.includes('in progress')) return { icon: Wrench, wrap: 'bg-indigo-50 text-indigo-600' }
  if (m.includes('accepted')) return { icon: CircleCheck, wrap: 'bg-sky-50 text-sky-600' }
  return { icon: Bell, wrap: 'bg-slate-100 text-slate-600' }
}
</script>
