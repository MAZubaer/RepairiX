// The device types RepairiX shops repair. Keep the values in sync with app/Support/DeviceType.php.
import { Headphones, Laptop, Smartphone, Tablet } from 'lucide-vue-next'

export const DEVICE_TYPES = [
  {
    value: 'phone',
    label: 'Phone',
    plural: 'Phones',
    icon: Smartphone,
    examples: 'iPhone, Samsung, Xiaomi, Pixel…',
    modelPlaceholder: 'e.g. iPhone 13 Pro',
    serialHint: 'Dial *#06# to see the IMEI, or check Settings › About phone.',
  },
  {
    value: 'laptop',
    label: 'Laptop',
    plural: 'Laptops',
    icon: Laptop,
    examples: 'MacBook, Dell, HP, Lenovo, ASUS…',
    modelPlaceholder: 'e.g. MacBook Air M1',
    serialHint: 'Usually on a sticker underneath, or in Settings › About.',
  },
  {
    value: 'tablet',
    label: 'Tablet',
    plural: 'Tablets',
    icon: Tablet,
    examples: 'iPad, Galaxy Tab, Lenovo Tab…',
    modelPlaceholder: 'e.g. iPad Air 5',
    serialHint: 'Settings › General › About on an iPad, or Settings › About tablet.',
  },
  {
    value: 'accessory',
    label: 'Accessory',
    plural: 'Accessories',
    icon: Headphones,
    examples: 'Earbuds, smartwatches, chargers, controllers…',
    modelPlaceholder: 'e.g. AirPods Pro',
    serialHint: 'Printed on the device or its box, if it has one.',
  },
]

const BY_VALUE = Object.fromEntries(DEVICE_TYPES.map((d) => [d.value, d]))

export function deviceMeta(value) {
  return BY_VALUE[value] || BY_VALUE.phone
}

/** Device type objects for a list of values, in the standard order. */
export function devicesFor(values) {
  const set = new Set(values?.length ? values : DEVICE_TYPES.map((d) => d.value))
  return DEVICE_TYPES.filter((d) => set.has(d.value))
}
