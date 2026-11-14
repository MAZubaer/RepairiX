<template>
  <AppLayout :title="shop.name">
    <Link href="/dashboard/customer" class="mb-5 inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
      <ArrowLeft class="size-4" />
      All shops
    </Link>

    <ShopProfileHeader :shop="shop" :logo-url="logo?.url">
      <template #actions>
        <button type="button" class="btn btn-primary btn-lg" @click="showModal = true">
          <Wrench class="size-5" />
          Request a repair
        </button>
      </template>
    </ShopProfileHeader>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
      <section class="card p-6 sm:p-8 lg:col-span-2">
        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-slate-100">
          <ListChecks class="size-5 text-brand-600 dark:text-brand-400" />
          Services
        </h2>
        <p v-if="shop.services_provided" class="mt-4 leading-relaxed whitespace-pre-line text-slate-600 dark:text-slate-400">{{ shop.services_provided }}</p>
        <p v-else class="mt-4 text-slate-400 dark:text-slate-500">This shop hasn't listed its services yet. Send a request and describe your problem.</p>
      </section>

      <aside class="card h-fit p-6 sm:p-8 lg:sticky lg:top-24">
        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">How it works</h2>
        <ol class="mt-5 space-y-4">
          <li v-for="(step, i) in howItWorks" :key="step" class="flex gap-3 text-sm text-slate-600 dark:text-slate-400">
            <span class="grid size-6 shrink-0 place-items-center rounded-full bg-brand-50 dark:bg-brand-500/10 text-xs font-bold text-brand-700 dark:text-brand-300">{{ i + 1 }}</span>
            {{ step }}
          </li>
        </ol>
        <button type="button" class="btn btn-primary mt-6 w-full" @click="showModal = true">Request a repair</button>
      </aside>
    </div>

    <section class="card mt-6 p-6 sm:p-8">
      <h2 class="mb-5 flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-slate-100">
        <ImageIcon class="size-5 text-brand-600 dark:text-brand-400" />
        Photos
      </h2>
      <ShopGallery :images="visibleGallery" />
    </section>

    <Modal :show="showModal" title="Request a repair" :subtitle="`Your request goes straight to ${shop.name}.`" @close="showModal = false">
      <form id="repair-request" class="space-y-4" novalidate @submit.prevent="submitRequest">
        <div v-if="form.errors.server" class="flex items-start gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 p-4 text-sm text-rose-700 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-500/30">
          <CircleAlert class="mt-0.5 size-4 shrink-0" />
          {{ form.errors.server }}
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
          <FormField id="phone_model" label="Phone model" :icon="Smartphone" :error="form.errors.phone_model">
            <template #default="{ id, inputClass }">
              <input :id="id" v-model="form.phone_model" type="text" required :class="inputClass" placeholder="e.g. iPhone 13 Pro" />
            </template>
          </FormField>
          <FormField id="phone_number" label="Contact number" :icon="Phone" :error="form.errors.phone_number">
            <template #default="{ id, inputClass }">
              <input :id="id" v-model="form.phone_number" type="tel" required :class="inputClass" placeholder="01XXXXXXXXX" />
            </template>
          </FormField>
        </div>
        <FormField
          id="phone_imei_number"
          label="IMEI number"
          :icon="Hash"
          :error="form.errors.phone_imei_number"
          hint="Dial *#06# on your phone to see it."
        >
          <template #default="{ id, inputClass }">
            <input :id="id" v-model="form.phone_imei_number" type="text" inputmode="numeric" required :class="inputClass" placeholder="15-digit IMEI" />
          </template>
        </FormField>
        <FormField id="customer_problem" label="What's wrong?" :error="form.errors.customer_problem">
          <template #default="{ id, inputClass }">
            <textarea :id="id" v-model="form.customer_problem" rows="4" required :class="inputClass" placeholder="Describe the issue, e.g. screen cracked after a drop, touch not working on the left side." />
          </template>
        </FormField>
      </form>
      <template #footer>
        <button type="button" class="btn btn-secondary" @click="showModal = false">Cancel</button>
        <button type="submit" form="repair-request" class="btn btn-primary" :disabled="form.processing">
          <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
          <Send v-else class="size-4" />
          Send request
        </button>
      </template>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import {
  ArrowLeft, CircleAlert, Hash, Image as ImageIcon, ListChecks, LoaderCircle, Phone, Send, Smartphone, Wrench,
} from 'lucide-vue-next'
import AppLayout from '../../Layouts/AppLayout.vue'
import FormField from '../../Components/FormField.vue'
import Modal from '../../Components/Modal.vue'
import ShopGallery from '../../Components/ShopGallery.vue'
import ShopProfileHeader from '../../Components/ShopProfileHeader.vue'

const props = defineProps({
  shop: { type: Object, required: true },
  logo: { type: Object, default: null },
  gallery: { type: Array, default: () => [] },
})

const showModal = ref(false)

const form = useForm({
  phone_model: '',
  phone_number: '',
  phone_imei_number: '',
  customer_problem: '',
})

const visibleGallery = computed(() => (Array.isArray(props.gallery) ? props.gallery : []).filter((i) => i && i.url))

const howItWorks = [
  'Send your phone model and the problem.',
  'The shop accepts and diagnoses it, and sets a price.',
  'Follow the repair live under “My repairs”.',
  'Accept delivery and rate the shop.',
]

function submitRequest() {
  form.post(`/customer/shops/${props.shop.shop_id}/requests`, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      showModal.value = false
    },
  })
}
</script>
