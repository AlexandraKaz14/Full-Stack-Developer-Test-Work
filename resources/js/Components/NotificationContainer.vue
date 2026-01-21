<script setup>
import { useNotification } from '@/composables/useNotification'

const { notifications, remove } = useNotification()

const typeClasses = {
    success: 'bg-green-500 text-white',
    error: 'bg-red-500 text-white',
    warning: 'bg-yellow-500 text-white',
    info: 'bg-blue-500 text-white'
}
</script>

<template>
    <div class="fixed top-4 right-4 z-50 space-y-3 max-w-md">
        <transition-group name="notification">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                :class="[
          'rounded-lg shadow-lg p-4 flex items-start space-x-3 cursor-pointer',
          typeClasses[notification.type]
        ]"
                @click="remove(notification.id)"
            >
                <div class="flex-1">
                    <p class="font-medium">{{ notification.message }}</p>
                </div>
                <button class="opacity-70 hover:opacity-100 text-lg">
                    &times;
                </button>
            </div>
        </transition-group>
    </div>
</template>

<style>
.notification-enter-active,
.notification-leave-active {
    transition: all 0.3s;
}
.notification-enter-from {
    opacity: 0;
    transform: translateX(100%);
}
.notification-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
