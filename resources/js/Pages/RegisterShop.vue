<template>
  <AuthLayout
    title="Register your shop"
    heading="Grow your repair business with RepairiX."
    tagline="Receive repair requests from nearby customers and manage every job from one dashboard."
    :points="[
      'Get discovered by customers looking for repairs',
      'Accept, diagnose, quote and update jobs in one place',
      'Customers get automatic status emails',
    ]"
  >
    <AccountTypeSwitch active="shop" class="mb-8" />

    <div class="mb-6">
      <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Register your shop</h1>
      <p class="mt-2 text-slate-500">After signing up you'll pick a monthly or yearly plan to go live.</p>
    </div>

    <div v-if="errors.server" class="mb-5 flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-200">
      <CircleAlert class="mt-0.5 size-4 shrink-0" />
      {{ errors.server }}
    </div>

    <form class="space-y-4" novalidate @submit.prevent="submit">
      <FormField id="shop_name" label="Shop name" :icon="Store" :error="errors.shop_name">
        <template #default="{ id, inputClass }">
          <input :id="id" v-model="form.shop_name" name="shop_name" type="text" autocomplete="organization" required :class="inputClass" placeholder="Enter your shop name" />
        </template>
      </FormField>

      <FormField id="email" label="Business email" :icon="Mail" :error="errors.email">
        <template #default="{ id, inputClass }">
          <input :id="id" v-model="form.email" name="email" type="email" autocomplete="email" required :class="inputClass" placeholder="Enter your business email" />
        </template>
      </FormField>

      <div class="grid gap-4 sm:grid-cols-2">
        <FormField id="phone" label="Phone number" :icon="Phone" :error="errors.phone">
          <template #default="{ id, inputClass }">
            <input :id="id" v-model="form.phone" name="phone" type="tel" autocomplete="tel" required :class="inputClass" placeholder="Enter your phone number" />
          </template>
        </FormField>

        <FormField id="shop_address" label="Shop address" :icon="MapPin" :error="errors.shop_address">
          <template #default="{ id, inputClass }">
            <input :id="id" v-model="form.shop_address" name="shop_address" type="text" autocomplete="street-address" required :class="inputClass" placeholder="Enter your shop address" />
          </template>
        </FormField>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <FormField id="password" label="Password" :icon="Lock" :error="errors.password" hint="At least 8 characters">
          <template #default="{ id, inputClass }">
            <input :id="id" v-model="form.password" name="password" type="password" autocomplete="new-password" required minlength="8" :class="inputClass" placeholder="Create a password" />
          </template>
        </FormField>

        <FormField id="password_confirmation" label="Confirm password" :icon="Lock" :error="errors.password_confirmation">
          <template #default="{ id, inputClass }">
            <input :id="id" v-model="form.password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="8" :class="inputClass" placeholder="Confirm your password" />
          </template>
        </FormField>
      </div>

      <button type="submit" class="btn btn-primary btn-lg mt-2 w-full" :disabled="form.processing">
        <LoaderCircle v-if="form.processing" class="size-5 animate-spin" />
        Create shop account
      </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
      Already registered?
      <Link href="/login" class="font-semibold text-brand-700 hover:text-brand-800">Log in</Link>
    </p>
  </AuthLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { CircleAlert, LoaderCircle, Lock, Mail, MapPin, Phone, Store } from 'lucide-vue-next'
import AuthLayout from '../Layouts/AuthLayout.vue'
import AccountTypeSwitch from '../Components/AccountTypeSwitch.vue'
import FormField from '../Components/FormField.vue'

const form = useForm({
  shop_name: '',
  email: '',
  phone: '',
  shop_address: '',
  password: '',
  password_confirmation: '',
})

const errors = computed(() => form.errors)

function submit() {
  form.post('/register/shop', {
    onError: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
