<template>
  <div class="grid grid-cols-2 gap-1 rounded-2xl bg-slate-100 p-1">
    <Link
      href="/register/customer"
      class="flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
      :class="active === 'customer' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
    >
      <Smartphone class="size-4" />
      Customer
    </Link>
    <Link
      href="/register/shop"
      class="flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
      :class="active === 'shop' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
    >
      <Store class="size-4" />
      Repair shop
    </Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Smartphone, Store } from 'lucide-vue-next'

defineProps({
  active: { type: String, required: true },
})
</script>
