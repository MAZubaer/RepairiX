<template>
  <AuthLayout
    title="Create your account"
    heading="Get your devices fixed by shops you can trust."
    tagline="Phone, laptop, tablet or accessory: compare repair shops, send a request in a minute, and follow every step until it's back."
  >
    <AccountTypeSwitch active="customer" class="mb-8" />

    <div class="mb-6">
      <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">Create your account</h1>
      <p class="mt-2 text-slate-500 dark:text-slate-400">It takes less than a minute.</p>
    </div>

    <div v-if="errors.server" class="mb-5 flex items-start gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 p-4 text-sm text-rose-700 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-500/30">
      <CircleAlert class="mt-0.5 size-4 shrink-0" />
      {{ errors.server }}
    </div>

    <form class="space-y-4" novalidate @submit.prevent="submit">
      <FormField id="name" label="Full name" :icon="User" :error="errors.name">
        <template #default="{ id, inputClass }">
          <input :id="id" v-model="form.name" name="name" type="text" autocomplete="name" required :class="inputClass" placeholder="Your full name" />
        </template>
      </FormField>

      <FormField id="email" label="Email address" :icon="Mail" :error="errors.email">
        <template #default="{ id, inputClass }">
          <input :id="id" v-model="form.email" name="email" type="email" autocomplete="email" required :class="inputClass" placeholder="you@example.com" />
        </template>
      </FormField>

      <div class="grid gap-4 sm:grid-cols-2">
        <FormField id="phone" label="Phone number" :icon="Phone" :error="errors.phone">
          <template #default="{ id, inputClass }">
            <input :id="id" v-model="form.phone" name="phone" type="tel" autocomplete="tel" required :class="inputClass" placeholder="01XXXXXXXXX" />
          </template>
        </FormField>

        <FormField id="location" label="Location" :icon="MapPin" :error="errors.location">
          <template #default="{ id, inputClass }">
            <input :id="id" v-model="form.location" name="location" type="text" autocomplete="address-level2" required :class="inputClass" placeholder="City or area" />
          </template>
        </FormField>
      </div>

      <FormField id="primary_device" label="Primary device" :icon="MonitorSmartphone" :error="errors.primary_device">
        <template #default="{ id, inputClass }">
          <input :id="id" v-model="form.primary_device" name="primary_device" type="text" required :class="inputClass" placeholder="e.g. iPhone 13, MacBook Air, iPad" />
        </template>
      </FormField>

      <div class="grid gap-4 sm:grid-cols-2">
        <FormField id="password" label="Password" :icon="Lock" :error="errors.password" hint="At least 8 characters">
          <template #default="{ id, inputClass }">
            <input :id="id" v-model="form.password" name="password" type="password" autocomplete="new-password" required minlength="8" :class="inputClass" placeholder="Create a password" />
          </template>
        </FormField>

        <FormField id="password_confirmation" label="Confirm password" :icon="Lock" :error="errors.password_confirmation">
          <template #default="{ id, inputClass }">
            <input :id="id" v-model="form.password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="8" :class="inputClass" placeholder="Repeat password" />
          </template>
        </FormField>
      </div>

      <button type="submit" class="btn btn-primary btn-lg mt-2 w-full" :disabled="form.processing">
        <LoaderCircle v-if="form.processing" class="size-5 animate-spin" />
        Create account
      </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
      Already have an account?
      <Link href="/login" class="font-semibold text-brand-700 dark:text-brand-300 hover:text-brand-800 dark:hover:text-brand-200">Log in</Link>
    </p>
  </AuthLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { CircleAlert, LoaderCircle, Lock, Mail, MapPin, Phone, MonitorSmartphone, User } from 'lucide-vue-next'
import AuthLayout from '../Layouts/AuthLayout.vue'
import AccountTypeSwitch from '../Components/AccountTypeSwitch.vue'
import FormField from '../Components/FormField.vue'

const form = useForm({
  name: '',
  email: '',
  phone: '',
  location: '',
  primary_device: '',
  password: '',
  password_confirmation: '',
})

const errors = computed(() => form.errors)

function submit() {
  form.post('/register/customer', {
    onError: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
