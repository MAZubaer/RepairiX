<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <Navbar />

    <main class="flex-1 flex items-center justify-center">
      <div class="w-full max-w-md px-6 py-12">
        <h2 class="text-3xl font-extrabold text-center">Login</h2>
        <p class="text-center text-gray-600 mb-6">Sign in with your email and password</p>

        <form @submit.prevent="submit" class="space-y-4 bg-white p-6 rounded-lg shadow-sm border">
          <div v-if="errors.server" class="text-red-600 text-sm mb-2">{{ errors.server }}</div>

          <div>
            <label class="block text-sm font-semibold mb-1">Email</label>
            <input
              v-model="form.email"
              required
              type="email"
              autocomplete="email"
              :class="['form-input', hasError('email') ? 'border-red-500' : '']"
              placeholder="Enter your email"
            />
            <div v-if="hasError('email')" class="text-red-500 text-sm mt-1">{{ errors.email[0] }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Password</label>
            <input
              v-model="form.password"
              required
              type="password"
              autocomplete="current-password"
              :class="['form-input', hasError('password') ? 'border-red-500' : '']"
              placeholder="Enter your password"
            />
            <div v-if="hasError('password')" class="text-red-500 text-sm mt-1">{{ errors.password[0] }}</div>
          </div>

          <div>
            <button type="submit" class="btn-primary w-full">Login</button>
          </div>
        </form>
      </div>
    </main>

    <footer class="h-12"></footer>
  </div>
</template>

<script setup>
import Navbar from '../../Components/Navbar.vue'
import { usePage } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import { reactive, computed } from 'vue'

const page = usePage()
const errors = computed(() => page.props.value.errors || {})

const form = reactive({
  email: '',
  password: '',
})

function submit() {
  Inertia.post('/login', form)
}

function hasError(field) {
  return Boolean(errors.value[field])
}
</script>

<style scoped>
.form-input{ width:100%; padding:.75rem 1rem; border:1px solid rgba(15,23,42,0.08); border-radius:.5rem; }
.form-input:focus{ outline:none; border-color: var(--color-primary); box-shadow: 0 0 0 4px rgba(20,184,166,0.08); }
</style>
