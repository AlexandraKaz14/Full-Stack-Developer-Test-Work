import { ref } from 'vue'

export function useNotification() {
    const notifications = ref([])

    const add = (message, type = 'info', duration = 3000) => {
        const id = Date.now()
        const notification = {
            id,
            message,
            type,
            duration,
        }

        notifications.value.push(notification)

        // Автоматическое удаление
        if (duration > 0) {
            setTimeout(() => {
                remove(id)
            }, duration)
        }

        return id
    }

    const success = (message, duration = 3000) => {
        return add(message, 'success', duration)
    }

    const error = (message, duration = 3000) => {
        return add(message, 'error', duration)
    }

    const info = (message, duration = 3000) => {
        return add(message, 'info', duration)
    }

    const warning = (message, duration = 3000) => {
        return add(message, 'warning', duration)
    }

    const remove = (id) => {
        const index = notifications.value.findIndex(n => n.id === id)
        if (index !== -1) {
            notifications.value.splice(index, 1)
        }
    }

    const clear = () => {
        notifications.value = []
    }

    return {
        notifications,
        add,
        success,
        error,
        info,
        warning,
        remove,
        clear,
    }
}
