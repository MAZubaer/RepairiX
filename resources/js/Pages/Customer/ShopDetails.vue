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
        <fieldset>
          <legend class="label">Device type</legend>
          <div class="grid gap-2" :class="({ 1: 'grid-cols-1', 2: 'grid-cols-2', 3: 'grid-cols-3' })[shopDevices.length] || 'grid-cols-2 sm:grid-cols-4'">
            <label
              v-for="device in shopDevices"
              :key="device.value"
              class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl px-2 py-3 text-sm font-semibold ring-1 transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-500"
              :class="form.device_type === device.value
                ? 'bg-brand-50 text-brand-700 ring-brand-500 dark:bg-brand-500/15 dark:text-brand-200 dark:ring-brand-400'
                : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800/60 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800'"
            >
              <input v-model="form.device_type" type="radio" name="device_type" :value="device.value" class="sr-only" />
              <component :is="device.icon" class="size-5" />
              {{ device.label }}
            </label>
          </div>
          <p v-if="form.errors.device_type" class="field-error">{{ form.errors.device_type }}</p>
        </fieldset>
        <div class="grid gap-4 sm:grid-cols-2">
          <FormField id="phone_model" :label="`${selectedDevice.label} model`" :icon="selectedDevice.icon" :error="form.errors.phone_model">
            <template #default="{ id, inputClass }">
              <input :id="id" v-model="form.phone_model" type="text" required :class="inputClass" :placeholder="selectedDevice.modelPlaceholder" />
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
          label="Serial / IMEI number (optional)"
          :icon="Hash"
          :error="form.errors.phone_imei_number"
          :hint="selectedDevice.serialHint"
        >
          <template #default="{ id, inputClass }">
            <input :id="id" v-model="form.phone_imei_number" type="text" :class="inputClass" :placeholder="form.device_type === 'phone' ? '15-digit IMEI' : 'Serial number'" />
          </template>
        </FormField>
        <FormField id="customer_problem" label="What's wrong?" :error="form.errors.customer_problem">
          <template #default="{ id, inputClass }">
            <textarea :id="id" v-model="form.customer_problem" rows="4" required :class="inputClass" :placeholder="problemPlaceholder" />
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
  ArrowLeft, CircleAlert, Hash, Image as ImageIcon, ListChecks, LoaderCircle, Phone, Send, Wrench,
} from 'lucide-vue-next'
import { deviceMeta, devicesFor } from '../../lib/devices'
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

const shopDevices = computed(() => devicesFor(props.shop.device_types))

const form = useForm({
  device_type: shopDevices.value[0]?.value || 'phone',
  phone_model: '',
  phone_number: '',
  phone_imei_number: '',
  customer_problem: '',
})

const visibleGallery = computed(() => (Array.isArray(props.gallery) ? props.gallery : []).filter((i) => i && i.url))

const selectedDevice = computed(() => deviceMeta(form.device_type))

const PROBLEM_EXAMPLES = {
  phone: 'e.g. screen cracked after a drop, touch not working on the left side.',
  laptop: "e.g. laptop shuts down when it gets hot, fan is very loud, won't charge.",
  tablet: 'e.g. battery drains overnight, charging port is loose.',
  accessory: "e.g. left earbud won't charge, watch strap clip broken.",
}
const problemPlaceholder = computed(() => `Describe the issue, ${PROBLEM_EXAMPLES[form.device_type] || PROBLEM_EXAMPLES.phone}`)

const howItWorks = [
  'Pick your device, then describe the problem.',
  'The shop accepts and diagnoses it, and sets a price.',
  'Follow the repair live under “My repairs”.',
  'Accept delivery and rate the shop.',
]

function submitRequest() {
  form.post(`/customer/shops/${props.shop.shop_id}/requests`, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('phone_model', 'phone_number', 'phone_imei_number', 'customer_problem')
      showModal.value = false
    },
  })
}
</script>
