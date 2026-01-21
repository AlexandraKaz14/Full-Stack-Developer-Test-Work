<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const show = ref(false)
const title = ref('')
const message = ref('')

let resolvePromise = null

const open = (modalTitle, modalMessage) => {
    return new Promise((resolve) => {
        title.value = modalTitle
        message.value = modalMessage
        show.value = true
        resolvePromise = resolve
    })
}

const confirm = (modalTitle, modalMessage) => {
    return open(modalTitle, modalMessage, 'warning')
}

const close = (result = false) => {
    show.value = false
    if (resolvePromise) {
        resolvePromise(result)
        resolvePromise = null
    }
}

defineExpose({ open, confirm, close })

const onConfirm = () => {
    close(true)
}

const onCancel = () => {
    close(false)
}

const handleKeyup = (e) => {
    if (e.key === 'Escape') {
        onCancel()
    }
}

onMounted(() => {
    document.addEventListener('keyup', handleKeyup)
})

onUnmounted(() => {
    document.removeEventListener('keyup', handleKeyup)
})
</script>

<template>
    <teleport to="body">
        <transition name="fade">
            <div
                v-if="show"
                class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
                @click.self="onCancel"
            >
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95"
                     :class="{ 'scale-100': show }">
                    <!-- Заголовок -->
                    <div class="flex items-center justify-between p-6 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">
                                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                        </div>
                        <button
                            @click="onCancel"
                            class="text-gray-400 hover:text-gray-600 text-2xl w-8 h-8 flex items-center justify-center"
                        >
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                    </div>

                    <!-- Сообщение -->
                    <div class="p-6">
                        <p class="text-gray-600">{{ message }}</p>
                    </div>

                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-3 p-6 border-t border-gray-200">
                        <button
                            @click="onCancel"
                            class="px-4 py-2 bg-gray-200 text-gray-700 hover:text-gray-900 font-medium rounded-lg hover:bg-gray-300 transition-colors"
                        >
                            Отмена
                        </button>
                        <button
                            @click="onConfirm"
                            class="px-6 py-2 font-medium bg-red-500 text-white rounded hover:bg-red-700 transition"
                        >
                          OK
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

