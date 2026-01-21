<script setup>
import { ref, onMounted } from 'vue'
import api from '@/composables/useApi'

const props = defineProps({
    id: Number,
    back: {
        type: String,
        default: '/admin/products'
    }
})

const form = ref({
    name: '',
    price: '',
    description: '',
    category_id: ''
})

const categories = ref([])
const loading = ref(false)

// Получить URL для возврата
const backUrl = ref(props.back || '/admin/products')

// Загрузка данных
const loadData = async () => {
    loading.value = true

    try {
        // Загружаем категории
        const catsResponse = await api.get('/categories')
        categories.value = catsResponse.data

        // Если редактируем товар
        if (props.id) {
            const productResponse = await api.get(`/products/${props.id}`)
            form.value = { ...productResponse.data }
            if (form.value.price) {
                form.value.price = parseFloat(form.value.price)
            }
        }
    } catch (err) {
        console.error('Ошибка:', err)
        alert('Ошибка загрузки данных')
    } finally {
        loading.value = false
    }
}

// Сохранение
const save = async () => {
    try {
        const data = {
            ...form.value,
            price: parseFloat(form.value.price) || 0
        }

        if (props.id) {
            await api.put(`/products/${props.id}`, data)
            alert('Товар успешно обновлен')
        } else {
            await api.post('/products', data)
            alert('Товар успешно создан')
        }

        // Возвращаемся назад
        window.location.href = backUrl.value

    } catch (err) {
        console.error('Ошибка:', err)
        alert('Ошибка при сохранении')
    }
}

onMounted(loadData)
</script>

<template>
    <div class="max-w-2xl mx-auto">
        <!-- Назад -->
        <div class="mb-6">
            <a :href="backUrl" class="text-blue-600 hover:text-blue-800">
                ← Назад
            </a>
        </div>

        <!-- Заголовок -->
        <h1 class="text-2xl font-bold text-gray-900 mb-6">
            {{ id ? 'Редактирование товара' : 'Создание товара' }}
        </h1>

        <!-- Форма -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="space-y-4">
                <!-- Название -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Название товара *
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Введите название"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <!-- Цена и категория -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Цена (₽) *
                        </label>
                        <input
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            placeholder="0.00"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Категория *
                        </label>
                        <select
                            v-model="form.category_id"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="" disabled>Выберите категорию</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Описание -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Описание
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        placeholder="Опишите товар..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 resize-y"
                    ></textarea>
                </div>

                <!-- Кнопки -->
                <div class="pt-4 border-t border-gray-200 flex gap-3">
                    <button
                        @click="save"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        {{ id ? 'Сохранить' : 'Создать' }}
                    </button>

                    <a
                        :href="backUrl"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                    >
                        Отмена
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
