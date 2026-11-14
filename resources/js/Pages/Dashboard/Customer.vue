<template>
  <AppLayout title="Find a repair shop">
    <div class="relative mb-10 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-700 to-slate-900 px-6 py-10 sm:px-10 sm:py-12">
      <div class="pointer-events-none absolute -top-20 -right-20 size-80 rounded-full bg-white/10 blur-3xl" />
      <div class="relative max-w-2xl">
        <p class="text-sm font-semibold text-brand-100">Welcome back, {{ firstName }}</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Find the right shop for your repair</h1>
        <p class="mt-2 text-brand-100/90">{{ shops.length }} active {{ shops.length === 1 ? 'shop' : 'shops' }} ready to take your request.</p>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
          <div class="flex-1">
            <SearchInput v-model="search" placeholder="Search by shop name or location…" />
          </div>
          <select v-model="sort" class="input sm:w-48" aria-label="Sort shops">
            <option value="rating">Top rated</option>
            <option value="newest">Newest</option>
            <option value="name">Name A–Z</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="filteredShops.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <Link
        v-for="shop in filteredShops"
        :key="shop.shop_id"
        :href="`/customer/shops/${shop.shop_id}`"
        class="card card-hover group flex flex-col overflow-hidden"
      >
        <div class="relative h-28 overflow-hidden bg-gradient-to-br" :class="coverGradient(shop.name)">
          <!-- Cover: the shop's first photo, else a blurred logo, else its colour pattern -->
          <img v-if="shop.cover_url" :src="shop.cover_url" alt="" loading="lazy" class="size-full object-cover transition duration-300 group-hover:scale-105" />
          <img v-else-if="shop.logo_url" :src="shop.logo_url" alt="" class="size-full scale-110 object-cover opacity-40 blur-md transition duration-300 group-hover:scale-125" />
          <div v-if="!shop.cover_url" class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.15)_1px,transparent_1px)] [background-size:16px_16px]" />
        </div>
        <div class="relative flex flex-1 flex-col px-5 pb-5">
          <Avatar :name="shop.name" :src="shop.logo_url" size="lg" rounded class="relative z-10 -mt-7 shadow-soft ring-4 ring-white" />
          <h3 class="mt-3 truncate text-lg font-bold text-slate-900 group-hover:text-brand-700">{{ shop.name }}</h3>
          <p class="mt-1 flex items-center gap-1.5 truncate text-sm text-slate-500">
            <MapPin class="size-4 shrink-0" />
            {{ shop.location }}
          </p>
          <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
            <StarRating v-if="shop.rating > 0" :model-value="shop.rating" show-value />
            <span v-else class="chip bg-slate-100 text-slate-500">New shop</span>
            <span class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700">
              View shop
              <ArrowRight class="size-4 transition group-hover:translate-x-0.5" />
            </span>
          </div>
        </div>
      </Link>
    </div>

    <div v-else class="card">
      <EmptyState
        :icon="SearchX"
        :title="shops.length ? 'No shops match your search' : 'No shops are available yet'"
        :description="shops.length ? `Nothing found for “${search}”. Try another name or area.` : 'Check back soon — new repair shops join regularly.'"
      >
        <button v-if="search" type="button" class="btn btn-secondary" @click="search = ''">Clear search</button>
      </EmptyState>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRight, MapPin, SearchX } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import Avatar from '../../Components/Avatar.vue'
import EmptyState from '../../Components/EmptyState.vue'
import SearchInput from '../../Components/SearchInput.vue'
import StarRating from '../../Components/StarRating.vue'
import { coverGradient } from '../../lib/format'

const props = defineProps({
  customerName: { type: String, default: 'Customer' },
  shops: { type: Array, default: () => [] },
})

const search = ref('')
const sort = ref('rating')
const firstName = computed(() => String(props.customerName || 'there').split(' ')[0])

const filteredShops = computed(() => {
  const q = search.value.trim().toLowerCase()
  const list = q
    ? props.shops.filter((s) => `${s.name} ${s.location}`.toLowerCase().includes(q))
    : [...props.shops]

  if (sort.value === 'rating') list.sort((a, b) => Number(b.rating) - Number(a.rating))
  if (sort.value === 'name') list.sort((a, b) => String(a.name).localeCompare(String(b.name)))
  // 'newest' keeps the server order (newest shop first).
  return list
})
</script>
