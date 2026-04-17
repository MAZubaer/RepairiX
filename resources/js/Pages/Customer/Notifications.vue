<template>
  <div class="min-h-screen bg-background text-dark flex flex-col">
    <CustomerNavbar />

    <main class="flex-1 py-10">
      <div class="w-full max-w-5xl mx-auto px-6">
        <h1 class="text-5xl font-extrabold leading-tight">Notifications</h1>
        <p class="mt-2 text-slate-600 text-lg">Latest updates from shop status changes</p>

        <section class="mt-6 space-y-3">
          <article
            v-for="note in notifications"
            :key="note.id"
            class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm"
          >
            <div class="flex items-start justify-between gap-3">
              <p class="text-[15px] text-slate-800 leading-relaxed">{{ note.message }}</p>
              <span class="inline-flex shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                {{ note.relative_time || note.time }}
              </span>
            </div>
            <p class="mt-2 text-xs text-slate-500">{{ note.time }}</p>
          </article>

          <div
            v-if="!notifications.length"
            class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-slate-500"
          >
            No notifications yet.
          </div>
        </section>
      </div>
    </main>

    <footer class="h-12"></footer>
  </div>
</template>

<script setup>
import CustomerNavbar from '../../Components/CustomerNavbar.vue'

defineProps({
  notifications: { type: Array, default: () => [] },
})
</script>

<style scoped>
</style>
