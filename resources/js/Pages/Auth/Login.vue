<template>
  <AuthLayout title="Log in">
    <div class="mb-8">
      <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Welcome back</h1>
      <p class="mt-2 text-slate-500">Log in to track repairs and manage your account.</p>
    </div>

    <form class="space-y-5" novalidate @submit.prevent="submit">
      <FormField id="email" label="Email address" :icon="Mail" :error="form.errors.email">
        <template #default="{ id, inputClass }">
          <input
            :id="id"
            v-model="form.email"
            type="email"
            autocomplete="email"
            required
            autofocus
            :class="inputClass"
            placeholder="you@example.com"
          />
        </template>
      </FormField>

      <FormField id="password" label="Password" :icon="Lock" :error="form.errors.password">
        <template #default="{ id, inputClass }">
          <input
            :id="id"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="current-password"
            required
            :class="[inputClass, 'pr-11']"
            placeholder="Enter your password"
          />
          <button
            type="button"
            class="absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400 hover:text-slate-600"
            :aria-label="showPassword ? 'Hide password' : 'Show password'"
            @click="showPassword = !showPassword"
          >
            <EyeOff v-if="showPassword" class="size-4" />
            <Eye v-else class="size-4" />
          </button>
        </template>
      </FormField>

      <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="form.processing">
        <LoaderCircle v-if="form.processing" class="size-5 animate-spin" />
        Log in
      </button>
    </form>

    <div class="mt-8 rounded-2xl bg-slate-50 p-5 text-sm ring-1 ring-slate-200/70">
      <p class="font-semibold text-slate-800">New to RepairiX?</p>
      <div class="mt-3 grid gap-2 sm:grid-cols-2">
        <Link href="/register/customer" class="btn btn-secondary btn-sm">
          <Smartphone class="size-4" />
          I need a repair
        </Link>
        <Link href="/register/shop" class="btn btn-secondary btn-sm">
          <Store class="size-4" />
          I run a repair shop
        </Link>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Eye, EyeOff, LoaderCircle, Lock, Mail, Smartphone, Store } from 'lucide-vue-next'
import AuthLayout from '../../Layouts/AuthLayout.vue'
import FormField from '../../Components/FormField.vue'

const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
})

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>
