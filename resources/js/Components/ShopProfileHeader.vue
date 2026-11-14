<template>
  <div class="card overflow-hidden">
    <div class="relative h-36 bg-gradient-to-br from-brand-500 via-brand-700 to-slate-900 sm:h-44">
      <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.12)_1px,transparent_1px)] [background-size:18px_18px]" />
    </div>
    <div class="px-6 pb-6 sm:px-8">
      <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:gap-5">
          <Avatar :name="shop.name" :src="logoUrl" size="xl" rounded class="relative z-10 -mt-12 shrink-0 shadow-lift ring-4 ring-white dark:ring-slate-900" />
          <div class="min-w-0 sm:pt-4">
            <h1 class="truncate text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl">{{ shop.name }}</h1>
            <p v-if="shop.motto" class="mt-1 text-slate-500 dark:text-slate-400 italic">“{{ shop.motto }}”</p>
            <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-600 dark:text-slate-400">
              <span class="inline-flex items-center gap-1.5">
                <MapPin class="size-4 text-slate-400 dark:text-slate-500" />
                {{ shop.address }}
              </span>
              <StarRating v-if="Number(shop.rating) > 0" :model-value="Number(shop.rating)" show-value />
              <span v-else class="chip bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">No ratings yet</span>
            </div>
          </div>
        </div>
        <div v-if="$slots.actions" class="flex shrink-0 gap-2 pb-1">
          <slot name="actions" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { MapPin } from 'lucide-vue-next'
import Avatar from './Avatar.vue'
import StarRating from './StarRating.vue'

defineProps({
  shop: { type: Object, required: true },
  logoUrl: { type: String, default: null },
})
</script>
