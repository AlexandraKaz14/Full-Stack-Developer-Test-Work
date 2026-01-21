import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { initializeAuth } from './composables/useAuth'
import AppLayout from './Layouts/AppLayout.vue'
import NotificationContainer from './Components/NotificationContainer.vue'
import './../css/app.css'

initializeAuth()

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        const page = pages[`./Pages/${name}.vue`]

        page.default.layout = page.default.layout || AppLayout

        return page
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () => h(App, props)
        })

        app.use(plugin)

        app.component('NotificationContainer', NotificationContainer)

        app.mount(el)
    },
})
