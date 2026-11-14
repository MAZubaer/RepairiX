import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import FlashToast from './Components/FlashToast.vue'

createInertiaApp({
	title: (title) => (title ? `${title} · RepairiX` : 'RepairiX'),
	resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
	setup({ el, App, props, plugin }) {
		// FlashToast lives outside the page so it survives navigation and never double-fires.
		createApp({ render: () => [h(App, props), h(FlashToast)] })
			.use(plugin)
			.mount(el)
	},
	progress: {
		color: '#0a9488',
		showSpinner: false,
	},
})
