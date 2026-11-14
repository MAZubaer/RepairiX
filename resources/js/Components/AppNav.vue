<template>
  <header class="sticky top-0 z-30 border-b border-slate-200/70 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl">
    <nav class="container-page flex h-16 items-center justify-between gap-4">
      <div class="flex items-center gap-8">
        <AppLogo :href="homeHref" />

        <div class="hidden items-center gap-1 md:flex">
          <Link
            v-for="link in links"
            :key="link.href"
            :href="link.href"
            class="relative inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
            :class="isActive(link) ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100'"
          >
            <component :is="link.icon" class="size-4" />
            {{ link.label }}
          </Link>
        </div>
      </div>

      <div class="hidden items-center gap-3 md:flex">
        <ThemeToggle />
        <template v-if="user">
          <div class="flex items-center gap-2.5 rounded-full py-1 pr-3 pl-1 ring-1 ring-slate-200 dark:ring-slate-800">
            <Avatar :name="user.name" size="sm" />
            <div class="leading-tight">
              <p class="max-w-[160px] truncate text-sm font-semibold text-slate-800 dark:text-slate-200">{{ user.name }}</p>
              <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 capitalize">{{ roleLabel }}</p>
            </div>
          </div>
          <button type="button" class="btn btn-ghost btn-sm" @click="logout">
            <LogOut class="size-4" />
            Log out
          </button>
        </template>
        <template v-else>
          <Link href="/login" class="btn btn-ghost">Log in</Link>
          <Link href="/register/customer" class="btn btn-primary">Get started</Link>
        </template>
      </div>

      <div class="-mr-2 flex items-center gap-1 md:hidden">
        <ThemeToggle />
        <button
          type="button"
          class="btn btn-ghost p-2"
          :aria-expanded="open"
          aria-label="Toggle navigation"
          @click="open = !open"
        >
          <X v-if="open" class="size-6" />
          <Menu v-else class="size-6" />
        </button>
      </div>
    </nav>

    <!-- Mobile menu -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-if="open" class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 md:hidden">
        <div class="container-page space-y-1 py-3">
          <div v-if="user" class="mb-3 flex items-center gap-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 p-3">
            <Avatar :name="user.name" />
            <div>
              <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ user.name }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ user.email }}</p>
            </div>
          </div>
          <Link
            v-for="link in links"
            :key="link.href"
            :href="link.href"
            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium"
            :class="isActive(link) ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-300' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/40'"
          >
            <component :is="link.icon" class="size-5" />
            {{ link.label }}
          </Link>
          <div class="pt-2">
            <button v-if="user" type="button" class="btn btn-secondary w-full" @click="logout">
              <LogOut class="size-4" />
              Log out
            </button>
            <div v-else class="grid grid-cols-2 gap-2">
              <Link href="/login" class="btn btn-secondary">Log in</Link>
              <Link href="/register/customer" class="btn btn-primary">Get started</Link>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </header>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
  Bell, Camera, ClipboardList, CreditCard, History, House, Info, LayoutDashboard, LogOut, Menu,
  Search, Store, User, Users, Wrench, X,
} from 'lucide-vue-next'
import AppLogo from './AppLogo.vue'
import Avatar from './Avatar.vue'
import ThemeToggle from './ThemeToggle.vue'

const page = usePage()
const open = ref(false)
const user = computed(() => page.props.auth?.user || null)

const NAV = {
  guest: [
    { label: 'Home', href: '/', icon: House, exact: true },
    { label: 'About', href: '/about', icon: Info },
  ],
  customer: [
    { label: 'Find shops', href: '/dashboard/customer', icon: Search, also: ['/customer/shops'] },
    { label: 'My repairs', href: '/update', icon: Wrench },
    { label: 'History', href: '/history', icon: History },
    { label: 'Notifications', href: '/notifications', icon: Bell },
  ],
  shop: [
    { label: 'Overview', href: '/dashboard/shop', icon: LayoutDashboard },
    { label: 'Requests', href: '/shop/customers', icon: ClipboardList },
    { label: 'Shop profile', href: '/shop/edit', icon: User },
    { label: 'Photos', href: '/shop/photos', icon: Camera },
    { label: 'Billing', href: '/shop/pricing', icon: CreditCard },
  ],
  admin: [
    { label: 'Overview', href: '/admin/dashboard', icon: LayoutDashboard },
    { label: 'Shops', href: '/admin/shops', icon: Store },
    { label: 'Requests', href: '/admin/service-requests', icon: ClipboardList },
    { label: 'Customers', href: '/admin/customers', icon: Users },
  ],
}

const links = computed(() => NAV[user.value?.role] || NAV.guest)

const homeHref = computed(() => {
  switch (user.value?.role) {
    case 'admin': return '/admin/dashboard'
    case 'shop': return '/dashboard/shop'
    case 'customer': return '/dashboard/customer'
    default: return '/'
  }
})

const roleLabel = computed(() => (user.value?.role === 'shop' ? 'Repair shop' : user.value?.role))

function isActive(link) {
  const path = page.url.split('?')[0]
  if (link.exact) return path === link.href
  return [link.href, ...(link.also || [])].some((p) => path === p || path.startsWith(`${p}/`))
}

watch(() => page.url, () => { open.value = false })

function logout() {
  router.post('/logout')
}
</script>
