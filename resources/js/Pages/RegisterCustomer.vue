<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <Navbar />

    <main class="flex-1 flex items-center justify-center">
      <div class="w-full max-w-md px-6 py-12">
        <h2 class="text-3xl font-extrabold text-center">Register as Customer</h2>
        <p class="text-center text-gray-600 mb-6">Create your account to get started</p>

        <form action="/register/customer" method="post" @submit="submit" class="space-y-4 bg-white p-6 rounded-lg shadow-sm border">
          <input type="hidden" name="_token" value="" />
          <div v-if="errors.server" class="text-red-600 text-sm mb-2">{{ errors.server }}</div>
          <div>
            <label class="block text-sm font-semibold mb-1">Full Name</label>
            <input name="name" v-model="form.name" required type="text" :class="['form-input', hasError('name') ? 'border-red-500' : '']" placeholder="Enter your full name" />
            <div v-if="hasError('name')" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Email</label>
            <input name="email" v-model="form.email" required type="email" :class="['form-input', hasError('email') ? 'border-red-500' : '']" placeholder="Enter your email" />
            <div v-if="hasError('email')" class="text-red-500 text-sm mt-1">{{ errors.email[0] }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Phone Number</label>
            <input name="phone" v-model="form.phone" required type="text" :class="['form-input', hasError('phone') ? 'border-red-500' : '']" placeholder="Enter your phone number" />
            <div v-if="hasError('phone')" class="text-red-500 text-sm mt-1">{{ errors.phone[0] }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Location</label>
            <input name="location" v-model="form.location" required type="text" :class="['form-input', hasError('location') ? 'border-red-500' : '']" placeholder="Enter your location" />
            <div v-if="hasError('location')" class="text-red-500 text-sm mt-1">{{ errors.location[0] }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Primary Device</label>
            <input name="primary_device" v-model="form.primary_device" required type="text" :class="['form-input', hasError('primary_device') ? 'border-red-500' : '']" placeholder="Enter your primary device" />
            <div v-if="hasError('primary_device')" class="text-red-500 text-sm mt-1">{{ errors.primary_device[0] }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Password</label>
            <input name="password" v-model="form.password" required minlength="8" type="password" :class="['form-input', hasError('password') ? 'border-red-500' : '']" placeholder="Create a password" />
            <div class="text-gray-500 text-xs mt-1">Minimum 8 characters</div>
            <div v-if="hasError('password')" class="text-red-500 text-sm mt-1">{{ errors.password[0] }}</div>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Confirm Password</label>
            <input name="password_confirmation" v-model="form.password_confirmation" required minlength="8" type="password" :class="['form-input', hasError('password_confirmation') ? 'border-red-500' : '']" placeholder="Confirm your password" />
            <div v-if="hasError('password_confirmation')" class="text-red-500 text-sm mt-1">{{ errors.password_confirmation[0] }}</div>
          </div>

          <div>
            <button type="submit" class="btn-primary w-full">Create Account</button>
          </div>
        </form>
        <!-- Inline fallback to populate the _token hidden input from XSRF cookie
             This runs even if the main app JS bundle fails to initialize. -->
        <script>
          (function(){
            try{
              var m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
              var tok = m ? decodeURIComponent(m[1]) : '';
              var inp = document.querySelector('form input[name="_token"]');
              if(inp) inp.value = tok;
            }catch(e){/* ignore */}
          })();
        </script>
      </div>
    </main>

    <footer class="h-12"></footer>
  </div>
</template>

<script setup>
import Navbar from '../Components/Navbar.vue'
import { usePage } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import { reactive, computed } from 'vue'

const page = usePage()
const errors = computed(() => page.props.value.errors || {})

const form = reactive({
  name: '',
  email: '',
  phone: '',
  location: '',
  primary_device: '',
  password: '',
  password_confirmation: '',
})

function submit(event) {
  if (Inertia && typeof Inertia.post === 'function') {
    event.preventDefault()
    Inertia.post('/register/customer', form)
    return
  }

  // Progressive fallback: if Inertia is unavailable, allow native HTML form submit.
}

function hasError(field) {
  return Boolean(errors.value[field])
}
</script>

<style scoped>
.form-input{ width:100%; padding:.75rem 1rem; border:1px solid rgba(15,23,42,0.08); border-radius:.5rem; }
.form-input:focus{ outline:none; border-color: var(--color-primary); box-shadow: 0 0 0 4px rgba(20,184,166,0.08); }
</style>
