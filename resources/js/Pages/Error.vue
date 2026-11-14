<template>
  <AppLayout :title="content.title">
    <div class="mx-auto flex max-w-lg flex-col items-center py-16 text-center">
      <span class="grid size-16 place-items-center rounded-2xl bg-brand-50 text-brand-600">
        <component :is="content.icon" class="size-8" />
      </span>
      <p class="mt-6 text-sm font-bold tracking-widest text-brand-600">ERROR {{ status }}</p>
      <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ content.title }}</h1>
      <p class="mt-3 text-slate-500">{{ content.text }}</p>
      <div class="mt-8 flex gap-3">
        <button type="button" class="btn btn-secondary" @click="goBack">
          <ArrowLeft class="size-4" />
          Go back
        </button>
        <Link :href="homeHref" class="btn btn-primary">
          <House class="size-4" />
          Home
        </Link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ArrowLeft, House, Lock, SearchX, ServerCrash, Wrench } from 'lucide-vue-next'
import AppLayout from '../Layouts/AppLayout.vue'

const props = defineProps({
  status: { type: Number, required: true },
})

const page = usePage()
const homeHref = computed(() => ({
  admin: '/admin/dashboard',
  shop: '/dashboard/shop',
  customer: '/dashboard/customer',
}[page.props.auth?.user?.role] || '/'))

const content = computed(() => ({
  403: { icon: Lock, title: 'Access denied', text: "You don't have permission to view this page." },
  404: { icon: SearchX, title: 'Page not found', text: "The page you're looking for doesn't exist or has moved." },
  500: { icon: ServerCrash, title: 'Something went wrong', text: 'An unexpected error happened on our side. Please try again.' },
  503: { icon: Wrench, title: 'Down for maintenance', text: "We're making improvements. Please check back shortly." },
}[props.status] || { icon: ServerCrash, title: 'Something went wrong', text: 'Please try again.' }))

function goBack() {
  if (window.history.length > 1) window.history.back()
  else window.location.href = homeHref.value
}
</script>
