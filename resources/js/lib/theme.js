// Night mode. The saved choice (or, if none, the device setting) is applied before first paint
// by the inline script in app.blade.php; this module keeps it in sync afterwards.
import { ref } from 'vue'

export const THEME_KEY = 'repairix-theme' // 'light' | 'dark'; missing = follow the device

const root = document.documentElement
const media = window.matchMedia('(prefers-color-scheme: dark)')

export const isDark = ref(root.classList.contains('dark'))

function savedTheme() {
  try {
    return localStorage.getItem(THEME_KEY)
  } catch {
    return null
  }
}

function apply(dark, animate = false) {
  if (animate) {
    root.classList.add('theme-transition')
    window.setTimeout(() => root.classList.remove('theme-transition'), 300)
  }
  root.classList.toggle('dark', dark)
  document.querySelector('meta[name="theme-color"]')?.setAttribute('content', dark ? '#020617' : '#0a9488')
  isDark.value = dark
}

export function toggleTheme() {
  const next = !isDark.value
  try {
    localStorage.setItem(THEME_KEY, next ? 'dark' : 'light')
  } catch {
    // Storage blocked (private mode): the switch still works for this page view.
  }
  apply(next, true)
}

// Follow the device setting live until the user picks a theme themselves.
media.addEventListener('change', (e) => {
  if (!savedTheme()) apply(e.matches, true)
})
