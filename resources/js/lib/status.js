// Single source of truth for repair-request statuses (labels, colours, progress steps).
// Class strings are written out in full so Tailwind can find them.

export const STATUS = {
  pending: {
    label: 'Pending',
    badge: 'bg-amber-50 text-amber-700 ring-amber-600/20',
    dot: 'bg-amber-500',
  },
  accepted: {
    label: 'Accepted',
    badge: 'bg-sky-50 text-sky-700 ring-sky-600/20',
    dot: 'bg-sky-500',
  },
  rejected: {
    label: 'Rejected',
    badge: 'bg-rose-50 text-rose-700 ring-rose-600/20',
    dot: 'bg-rose-500',
  },
  'in progress': {
    label: 'In progress',
    badge: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
    dot: 'bg-indigo-500',
  },
  completed: {
    label: 'Completed',
    badge: 'bg-violet-50 text-violet-700 ring-violet-600/20',
    dot: 'bg-violet-500',
  },
  'sent from shop': {
    label: 'Sent from shop',
    badge: 'bg-teal-50 text-teal-700 ring-teal-600/20',
    dot: 'bg-teal-500',
  },
  delivered: {
    label: 'Delivered',
    badge: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    dot: 'bg-emerald-500',
  },
}

const FALLBACK = { label: 'Unknown', badge: 'bg-slate-100 text-slate-700 ring-slate-500/20', dot: 'bg-slate-400' }

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
