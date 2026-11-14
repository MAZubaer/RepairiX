<template>
  <div class="grid grid-cols-2 gap-1 rounded-2xl bg-slate-100 dark:bg-slate-800 p-1">
    <Link
      href="/register/customer"
      class="flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
      :class="active === 'customer' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
    >
      <MonitorSmartphone class="size-4" />
      Customer
    </Link>
    <Link
      href="/register/shop"
      class="flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
      :class="active === 'shop' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
    >
      <Store class="size-4" />
      Repair shop
    </Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { MonitorSmartphone, Store } from 'lucide-vue-next'

defineProps({
  active: { type: String, required: true },
})
</script>
