<script setup>
import { ref, computed } from 'vue'
import { useAuth } from '@/composables/useAuth'
import NotificationContainer from '@/Components/NotificationContainer.vue'

const { isAuthenticated, logout } = useAuth()
const mobileMenuOpen = ref(false)

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Навигация -->
        <nav class="bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">
                    <!-- Логотип -->
                    <a href="/" class="flex items-center space-x-2 text-xl font-bold hover:opacity-90 transition-opacity">
                        <span>Продукты</span>
                    </a>

                    <!-- Десктопное меню -->
                    <div class="hidden md:flex items-center space-x-6">
                        <a href="/" class="hover:text-blue-100 transition-colors">Каталог</a>

                        <template v-if="isAuthenticated">
                            <a href="/admin/products" class="hover:text-blue-100 transition-colors">Управление</a>
                            <button @click="logout" class="btn btn-outline !border-white !text-white hover:!bg-white/10">
                                Выйти
                            </button>
                        </template>
                        <template v-else>
                            <a href="/login" class="btn btn-primary">
                                Войти
                            </a>
                        </template>
                    </div>

                    <!-- Мобильная кнопка меню -->
                    <button @click="toggleMobileMenu" class="md:hidden text-2xl">
                        ☰
                    </button>
                </div>

                <!-- Мобильное меню -->
                <div v-if="mobileMenuOpen" class="md:hidden py-4 space-y-3 border-t border-white/20">
                    <a href="/" class="block hover:text-blue-100 transition-colors">Каталог</a>

                    <template v-if="isAuthenticated">
                        <a href="/admin/products" class="block hover:text-blue-100 transition-colors">Управление</a>
                        <button @click="logout" class="btn btn-outline !border-white !text-white hover:!bg-white/10 w-full">
                            Выйти
                        </button>
                    </template>
                    <template v-else>
                        <a href="/login" class="btn btn-primary w-full">
                            Войти
                        </a>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Основной контент -->
        <main class="flex-1 container mx-auto px-4 py-6">
            <slot />
        </main>

        <!-- Уведомления -->
        <NotificationContainer />

        <!-- Футер -->
        <footer class="bg-gradient-to-r from-gray-800 to-gray-900 text-white py-6 mt-12">
            <div class="container mx-auto px-4 text-center">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="flex items-center space-x-2 mb-4 md:mb-0">
                        <span class="font-semibold">Каталог продуктов</span>
                    </div>
                    <div class="text-gray-400 text-sm">
                        © {{ new Date().getFullYear() }}. Тестовое задание
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.container {
    max-width: 1200px;
}
</style>
