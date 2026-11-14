<template>
  <AppLayout title="Plans & billing">
    <div class="mx-auto max-w-5xl">
      <div class="text-center">
        <span class="eyebrow">
          <CreditCard class="size-3.5" />
          Plans & billing
        </span>
        <h1 class="mt-5 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
          {{ isActive ? 'Your subscription' : 'Choose a plan to go live' }}
        </h1>
        <p class="mx-auto mt-3 max-w-2xl text-slate-500">
          An active plan lets customers find your shop and unlocks your dashboard, profile and request management.
        </p>
      </div>

      <div
        v-if="isActive"
        class="mt-8 flex flex-col items-start justify-between gap-4 rounded-2xl bg-emerald-50 p-5 ring-1 ring-emerald-200 sm:flex-row sm:items-center"
      >
        <div class="flex items-center gap-3">
          <span class="grid size-10 place-items-center rounded-full bg-emerald-100 text-emerald-700"><BadgeCheck class="size-5" /></span>
          <div>
            <p class="font-semibold text-emerald-900">Your shop is live on the {{ shop.subscription_plan || 'current' }} plan</p>
            <p class="text-sm text-emerald-800/80">Active until {{ formatDate(shop.expiry_date) }}</p>
          </div>
        </div>
        <Link href="/dashboard/shop" class="btn btn-primary">
          Go to dashboard
          <ArrowRight class="size-4" />
        </Link>
      </div>

      <div v-else-if="displayMessage" class="mt-8 flex items-start gap-3 rounded-2xl p-4 text-sm font-medium ring-1" :class="messageClass">
        <Info class="mt-0.5 size-4 shrink-0" />
        {{ displayMessage }}
      </div>

      <div class="mt-10 grid gap-6 md:grid-cols-2">
        <article
          v-for="plan in plans"
          :key="plan.id"
          class="relative flex flex-col rounded-3xl p-8"
          :class="plan.featured ? 'bg-slate-950 text-white shadow-lift' : 'card'"
        >
          <span v-if="plan.featured" class="absolute -top-3 right-8 chip bg-brand-500 text-white shadow-sm">
            <Sparkles class="size-3.5" />
            Best value
          </span>
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold">{{ plan.name }}</h2>
            <span v-if="isActive && shop.subscription_plan === plan.id" class="chip bg-emerald-500/15 text-emerald-500">Current plan</span>
          </div>
          <p class="mt-1 text-sm" :class="plan.featured ? 'text-slate-400' : 'text-slate-500'">{{ plan.blurb }}</p>
          <p class="mt-6 flex items-end gap-1">
            <span class="text-5xl font-extrabold tracking-tight">{{ plan.price }}</span>
            <span class="mb-1.5 text-sm" :class="plan.featured ? 'text-slate-400' : 'text-slate-500'">/{{ plan.period }}</span>
          </p>
          <ul class="mt-8 flex-1 space-y-3 text-sm">
            <li v-for="f in plan.features" :key="f" class="flex items-start gap-3">
              <Check class="mt-0.5 size-4 shrink-0" :class="plan.featured ? 'text-brand-400' : 'text-brand-600'" stroke-width="3" />
              <span :class="plan.featured ? 'text-slate-200' : 'text-slate-700'">{{ f }}</span>
            </li>
          </ul>
          <button
            type="button"
            class="btn btn-lg mt-8 w-full"
            :class="plan.featured ? 'bg-brand-500 text-white hover:bg-brand-400' : 'btn-primary'"
            :disabled="loadingPlan !== null || isActive"
            @click="startCheckout(plan.id)"
          >
            <LoaderCircle v-if="loadingPlan === plan.id" class="size-5 animate-spin" />
            {{ buttonLabel(plan) }}
          </button>
        </article>
      </div>

      <p class="mt-6 flex items-center justify-center gap-2 text-sm text-slate-500">
        <Lock class="size-4" />
        Payments are processed securely by Stripe.
      </p>

      <div class="mt-14 grid gap-4 sm:grid-cols-3">
        <div v-for="item in faq" :key="item.q" class="card p-6">
          <p class="font-semibold text-slate-900">{{ item.q }}</p>
          <p class="mt-2 text-sm text-slate-500">{{ item.a }}</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { ArrowRight, BadgeCheck, Check, CreditCard, Info, LoaderCircle, Lock, Sparkles } from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import { formatDate } from '../../lib/format'

const props = defineProps({
  shop: { type: Object, required: true },
  stripe: { type: Object, required: true },
  message: { type: String, default: null },
})

const loadingPlan = ref(null)

const isActive = computed(() => {
  if (props.shop.subscription_status !== 'activated' || !props.shop.expiry_date) return false
  return new Date(props.shop.expiry_date).getTime() > Date.now()
})

const displayMessage = computed(() => ({
  checkout_cancelled: 'Checkout was cancelled. Choose a plan whenever you are ready.',
  payment_processing: 'Payment received! We are confirming your subscription — refresh in a few seconds.',
}[props.message] || null))

const messageClass = computed(() => (props.message === 'payment_processing'
  ? 'bg-emerald-50 text-emerald-800 ring-emerald-200'
  : 'bg-amber-50 text-amber-800 ring-amber-200'))

const plans = [
  {
    id: 'monthly',
    name: 'Monthly',
    blurb: 'Flexible plan for growing repair shops.',
    price: '৳499',
    period: 'month',
    features: ['Unlimited service requests', 'Customer update workflow', 'Status email notifications', 'Priority support'],
  },
  {
    id: 'yearly',
    name: 'Yearly',
    blurb: 'Save more with annual billing.',
    price: '৳4,999',
    period: 'year',
    featured: true,
    features: ['Everything in Monthly', '2 months free compared to monthly', 'Dedicated yearly billing support', 'Best long-term value'],
  },
]

const faq = [
  { q: 'When does my shop go live?', a: 'As soon as Stripe confirms your payment, usually within a few seconds.' },
  { q: 'How do I pay?', a: 'Checkout runs on Stripe, so your card details never touch RepairiX.' },
  { q: 'What happens if it expires?', a: 'Your shop is hidden from customers until you renew. Your data is kept.' },
]

function buttonLabel(plan) {
  if (loadingPlan.value === plan.id) return 'Redirecting to Stripe…'
  if (isActive.value) return props.shop.subscription_plan === plan.id ? 'Your current plan' : 'Available after your plan ends'
  return `Subscribe ${plan.name.toLowerCase()}`
}

function startCheckout(plan) {
  loadingPlan.value = plan
  router.post('/shop/billing/checkout', { plan }, {
    onFinish: () => { loadingPlan.value = null },
  })
}
</script>
