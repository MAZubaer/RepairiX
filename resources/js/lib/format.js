export function formatMoney(value, fallback = '—') {
  if (value === null || value === undefined || value === '') return fallback
  const n = Number(value)
  if (Number.isNaN(n)) return String(value)
  return `৳${n.toLocaleString('en-US', { maximumFractionDigits: 2 })}`
}

export function formatNumber(value) {
  return Number(value || 0).toLocaleString('en-US')
}

export function formatDate(value) {
  if (!value) return '—'
  const d = new Date(value.length === 10 ? `${value}T00:00:00` : value)
  if (Number.isNaN(d.getTime())) return value
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

export function initials(name) {
  return String(name || '?')
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() || '')
    .join('')
}

const COVER_GRADIENTS = [
  'from-brand-500 to-brand-800',
  'from-sky-500 to-indigo-700',
  'from-violet-500 to-fuchsia-700',
  'from-amber-400 to-orange-600',
  'from-emerald-500 to-teal-700',
  'from-rose-500 to-pink-700',
]

function hashOf(name) {
  const s = String(name || '')
  let hash = 0
  for (let i = 0; i < s.length; i++) hash = (hash * 31 + s.charCodeAt(i)) >>> 0
  return hash
}

/** Stable cover gradient per shop name. */
export function coverGradient(name) {
  return COVER_GRADIENTS[hashOf(name) % COVER_GRADIENTS.length]
}

const AVATAR_TONES = [
  'bg-brand-100 dark:bg-brand-500/15 text-brand-800 dark:text-brand-200',
  'bg-sky-100 dark:bg-sky-500/15 text-sky-800 dark:text-sky-200',
  'bg-violet-100 dark:bg-violet-500/15 text-violet-800 dark:text-violet-200',
  'bg-amber-100 dark:bg-amber-500/15 text-amber-800 dark:text-amber-200',
  'bg-rose-100 dark:bg-rose-500/15 text-rose-800 dark:text-rose-200',
  'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-800 dark:text-emerald-200',
]

/** Stable colour per name, so the same person always gets the same avatar colour. */
export function avatarTone(name) {
  return AVATAR_TONES[hashOf(name) % AVATAR_TONES.length]
}
