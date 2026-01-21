<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import api from '@/composables/useApi'
import { useNotification } from '@/composables/useNotification'
import SimpleModal from '@/Components/SimpleModal.vue'

const props = defineProps({
    isAdmin: {
        type: Boolean,
        default: false
    },
    showFilters: {
        type: Boolean,
        default: true
    }
})

// Состояние
const products = ref([])
const categories = ref([])
const selectedCategory = ref('')
const searchQuery = ref('')
const loading = ref(false)
const error = ref(null)

// Пагинация
const currentPage = ref(1)
const lastPage = ref(1)

// Рефы
const modalRef = ref(null)
const notification = useNotification()

// Получаем категории
const loadCategories = async () => {
    try {
        const response = await api.get('/categories')
        categories.value = [{ id: '', name: 'Все категории' }, ...response.data]
    } catch (err) {
        console.error('Ошибка загрузки категорий:', err)
        notification.error('Не удалось загрузить категории')
    }
}

// Загрузка товаров
const loadProducts = async (page = 1) => {
    loading.value = true
    error.value = null
    currentPage.value = page

    try {
        let url = `/products?page=${page}`

        if (selectedCategory.value) {
            url += `&category_id=${selectedCategory.value}`
        }

        if (searchQuery.value) {
            url += `&search=${encodeURIComponent(searchQuery.value)}`
        }

        const response = await api.get(url)

        const productsData = response.data.data || response.data
        products.value = productsData

        // Пагинация
        if (response.data.meta) {
            lastPage.value = response.data.meta.last_page
        } else if (response.data.last_page) {
            lastPage.value = response.data.last_page
        }

    } catch (err) {
        error.value = 'Ошибка загрузки товаров'
        notification.error('Не удалось загрузить товары')
        console.error('Load products error:', err)
    } finally {
        loading.value = false
    }
}

// Удаление товара
const deleteProduct = async (id) => {
    try {
        if (!modalRef.value) {
            console.error('Modal ref is not available')
            return
        }

        const confirmed = await modalRef.value.confirm(
            'Удаление товара',
            'Вы уверены, что хотите удалить этот товар? Это действие нельзя отменить.'
        )

        if (!confirmed) return

        await api.delete(`/products/${id}`)
        notification.success('Товар успешно удален')
        await loadProducts(currentPage.value)

    } catch (err) {
        console.error('Delete product error:', err)
        notification.error('Ошибка при удалении товара')
    }
}

// Поиск с дебаунсом
let searchTimeout = null
const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        loadProducts(1)
    }, 500)
}

// Следим за изменениями фильтров
watch([selectedCategory], () => {
    loadProducts(1)
})

// Навигация по страницам
const goToPage = (page) => {
    if (page >= 1 && page <= lastPage.value) {
        loadProducts(page)
    }
}

// Очистка таймера
onBeforeUnmount(() => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
})

// Инициализация
onMounted(async () => {
    await loadCategories()
    await loadProducts()
})
</script>

<template>
    <div>
        <!-- Заголовок -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                {{ isAdmin ? 'Управление товарами' : 'Каталог товаров' }}
            </h1>

            <!-- Кнопка добавления -->
            <div v-if="isAdmin" class="mt-4 sm:mt-0">
                <a href="/admin/products/create" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    Добавить товар
                </a>
            </div>
        </div>

        <!-- Фильтры -->
        <div v-if="showFilters" class="card mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Поиск -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Поиск товаров
                    </label>
                    <input
                        v-model="searchQuery"
                        @input="debouncedSearch"
                        type="text"
                        class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors"
                        placeholder="Введите название товара..."
                    />
                </div>

                <!-- Фильтр по категории -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                       Категория
                    </label>
                    <select
                        v-model="selectedCategory"
                        class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white appearance-none"
                    >
                        <option v-for="category in categories"
                                :key="category.id"
                                :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Сообщения об ошибках -->
        <div v-if="error" class="p-4 rounded-lg border-l-4 border-red-500 bg-red-50 text-red-700 flex items-start space-x-3 mb-6">
            <span>{{ error }}</span>
        </div>

        <!-- Загрузка -->
        <div v-if="loading" class="bg-white rounded-xl shadow-lg p-6 text-center py-12">
            <div class="animate-spin rounded-full border-4 border-gray-300 border-t-blue-600 h-12 w-12 mx-auto mb-4"></div>
            <p class="text-gray-600">Загрузка товаров...</p>
        </div>

        <!-- Список товаров -->
        <div v-else-if="products.length">
            <div class="bg-white rounded-xl shadow-lg p-6 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                        <tr>
                            <th class="bg-gray-50 text-left py-3 px-4 font-semibold text-gray-700 border-b border-gray-200 w-20">ID</th>
                            <th class="bg-gray-50 text-left py-3 px-4 font-semibold text-gray-700 border-b border-gray-200">Название</th>
                            <th class="bg-gray-50 text-left py-3 px-4 font-semibold text-gray-700 border-b border-gray-200 w-32">Цена</th>
                            <th class="bg-gray-50 text-left py-3 px-4 font-semibold text-gray-700 border-b border-gray-200 w-48">Категория</th>
                            <th class="bg-gray-50 text-left py-3 px-4 font-semibold text-gray-700 border-b border-gray-200 w-40">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                            <td class="py-3 px-4 border-b border-gray-100 text-gray-500 font-mono">#{{ product.id }}</td>
                            <td class="py-3 px-4 border-b border-gray-100">
                                <a v-if="!isAdmin"
                                   :href="`/products/${product.id}`"
                                   class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                    {{ product.name }}
                                </a>
                                <span v-else class="font-medium">{{ product.name }}</span>
                            </td>
                            <td class="py-3 px-4 border-b border-gray-100 text-green-600 font-semibold">
                                {{ product.price?.toLocaleString('ru-RU') }} ₽
                            </td>
                            <td class="py-3 px-4 border-b border-gray-100">
                                    <span v-if="product.category_id"
                                          class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                        {{ categories.find(c => c.id === product.category_id)?.name || 'Без категории' }}
                                    </span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="py-3 px-4 border-b border-gray-100">
                                <div class="flex space-x-2">
                                    <template v-if="isAdmin">
                                        <a :href="`/admin/products/${product.id}/edit`"
                                           class="group relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-blue-200 text-blue-700 rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-800 transition-all duration-300 hover:scale-[1.02]"
                                           title="Редактировать">
                                            <i class="fas fa-pencil"></i>
                                            <span class="absolute inset-0 rounded-lg border-2 border-blue-300 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                                        </a>

                                        <button @click="() => deleteProduct(product.id)"
                                                class="group relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-red-200 text-red-700 rounded-lg hover:bg-red-50 hover:border-red-300 hover:text-red-800 transition-all duration-300 hover:scale-[1.02]"
                                                title="Удалить">
                                            <i class="fa fa-trash"></i>
                                            <span class="absolute inset-0 rounded-lg border-2 border-red-300 opacity-0 group-hover:opacity-100 transition-opacity"></span>

                                        </button>
                                    </template>
                                    <template v-else>
                                        <a :href="`/products/${product.id}`"
                                           class="inline-flex items-center justify-center px-3 py-1.5 border-2 border-blue-600 text-blue-600 hover:bg-blue-50 rounded-lg text-sm font-medium transition-colors">
                                            Подробнее
                                        </a>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Пагинация -->
            <div v-if="lastPage > 1" class="flex items-center justify-center space-x-2 mt-6">
                <button @click="() => goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    ←
                </button>

                <div class="flex items-center space-x-1">
                    <button v-for="pageNum in Math.min(5, lastPage)"
                            :key="pageNum"
                            @click="() => goToPage(pageNum)"
                            :class="[
                                'px-3 py-1 rounded-lg font-medium transition-colors',
                                currentPage === pageNum
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-700 hover:bg-gray-100'
                            ]">
                        {{ pageNum }}
                    </button>

                    <span v-if="lastPage > 5" class="px-2 text-gray-500">...</span>

                    <button v-if="lastPage > 5"
                            @click="() => goToPage(lastPage)"
                            :class="[
                                'px-3 py-1 rounded-lg font-medium transition-colors',
                                currentPage === lastPage
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-700 hover:bg-gray-100'
                            ]">
                        {{ lastPage }}
                    </button>
                </div>

                <button @click="() => goToPage(currentPage + 1)"
                        :disabled="currentPage === lastPage"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    →
                </button>
            </div>
        </div>

        <!-- Нет товаров -->
        <div v-else class="bg-white rounded-xl shadow-lg p-6 text-center py-12">
            <div class="text-5xl mb-4 opacity-30"><i class="fa fa-search" aria-hidden="true"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Товары не найдены</h3>
            <p class="text-gray-500">
                {{ selectedCategory || searchQuery ? 'Попробуйте изменить параметры поиска' : 'В каталоге пока нет товаров' }}
            </p>
        </div>

        <!-- Модалка -->
        <SimpleModal ref="modalRef" />
    </div>
</template>
