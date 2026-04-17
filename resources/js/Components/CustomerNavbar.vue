<template>
  <nav class="w-full border-b border-gray-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16 items-center">
        <div class="flex items-center">
          <a href="/" class="inline-flex items-center">
            <img :src="logo" alt="RepairiX" class="h-8 w-8 object-contain" />
          </a>
        </div>
        <div class="flex items-center space-x-4">
          <a href="/update" class="text-sm text-dark hover:underline">Updates</a>
          <a href="/history" class="text-sm text-dark hover:underline">History</a>
          <a href="#about" class="text-sm text-dark hover:underline">About Us</a>
          <button @click="logout" type="button" class="inline-flex items-center px-3 py-1 rounded-md bg-red-500 text-white text-sm">Logout</button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { Inertia } from '@inertiajs/inertia'

const logo = '/images/repairix_short.png'

function logout() {
  Inertia.post('/logout')
}
</script>

<style scoped>
</style>
