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
          <a href="#about" class="text-sm text-dark hover:underline">About Us</a>
          <a href="/login" class="inline-flex items-center px-4 py-2 rounded-md bg-primary text-white text-sm hover:opacity-90">Login</a>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
const logo = '/images/repairix_short.png'
</script>

<style scoped>
</style>
