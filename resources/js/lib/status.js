// Single source of truth for repair-request statuses (labels, colours, progress steps).
// Class strings are written out in full so Tailwind can find them.

export const STATUS = {
  pending: {
    label: 'Pending',
    badge: 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 ring-amber-600/20 dark:ring-amber-400/25',
    dot: 'bg-amber-500',
  },
  accepted: {
    label: 'Accepted',
    badge: 'bg-sky-50 dark:bg-sky-500/10 text-sky-700 dark:text-sky-300 ring-sky-600/20 dark:ring-sky-400/25',
    dot: 'bg-sky-500',
  },
  rejected: {
    label: 'Rejected',
    badge: 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-300 ring-rose-600/20 dark:ring-rose-400/25',
    dot: 'bg-rose-500',
  },
  'in progress': {
    label: 'In progress',
    badge: 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 ring-indigo-600/20 dark:ring-indigo-400/25',
    dot: 'bg-indigo-500',
  },
  completed: {
    label: 'Completed',
    badge: 'bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300 ring-violet-600/20 dark:ring-violet-400/25',
    dot: 'bg-violet-500',
  },
  'sent from shop': {
    label: 'Sent from shop',
    badge: 'bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 ring-teal-600/20 dark:ring-teal-400/25',
    dot: 'bg-teal-500',
  },
  delivered: {
    label: 'Delivered',
    badge: 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 ring-emerald-600/20 dark:ring-emerald-400/25',
    dot: 'bg-emerald-500',
  },
}

const FALLBACK = { label: 'Unknown', badge: 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 ring-slate-500/20 dark:ring-slate-400/20', dot: 'bg-slate-400' }

export function statusMeta(status) {
  return STATUS[status] || { ...FALLBACK, label: status || FALLBACK.label }
}

/** The happy-path order a request moves through, with customer-friendly step names. */
export const STEPS = [
  { status: 'pending', label: 'Requested' },
  { status: 'accepted', label: 'Accepted' },
  { status: 'in progress', label: 'Repairing' },
  { status: 'completed', label: 'Repaired' },
  { status: 'sent from shop', label: 'On its way' },
  { status: 'delivered', label: 'Delivered' },
]

export function stepIndex(status) {
  return STEPS.findIndex((s) => s.status === status)
}

export const ACTIVE_STATUSES = ['pending', 'accepted', 'in progress', 'completed', 'sent from shop']
export const DONE_STATUSES = ['delivered', 'rejected']
