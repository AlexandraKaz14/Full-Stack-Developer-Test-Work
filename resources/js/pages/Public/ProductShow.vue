<script setup>
import { ref, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import api from '@/composables/useApi'

const props = defineProps({
    id: Number
})

// Состояние
const product = ref(null)
const categories = ref([])
const loading = ref(true)
const error = ref(null)

// Категория товара
const productCategory = computed(() => {
    if (!product.value || !categories.value.length) return null
    return categories.value.find(cat => cat.id === product.value.category_id)
})

// Загрузка данных
const loadData = async () => {
    try {
        loading.value = true
        error.value = null

        // Загружаем товар
        const productResponse = await api.get(`/products/${props.id}`)
        product.value = productResponse.data

        // Загружаем категории
        const categoriesResponse = await api.get('/categories')
        categories.value = categoriesResponse.data

    } catch (err) {
        console.error('Load product error:', err)

        if (err.response?.status === 404) {
            error.value = 'Товар не найден'
        } else {
            error.value = 'Произошла ошибка при загрузке товара'
        }

    } finally {
        loading.value = false
    }
}

// Инициализация
onMounted(loadData)
</script>

<template>
    <div>
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="/" class="breadcrumb-link">
                ← Назад к каталогу
            </a>
        </div>

        <!-- Загрузка -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Загрузка товара...</p>
        </div>

        <!-- Ошибка -->
        <div v-else-if="error" class="error-state">
            <div class="error-icon"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
            </div>
            <h3>Ошибка</h3>
            <p>{{ error }}</p>
            <button @click="loadData" class="btn btn-secondary">
                Попробовать снова
            </button>
        </div>

        <!-- Карточка товара -->
        <div v-else-if="product" class="product-card">
            <!-- Заголовок -->
            <div class="product-header">
                <div class="product-meta">
                    <span class="product-id">ID: {{ product.id }}</span>
                    <span
                        v-if="productCategory"
                        class="product-category-tag"
                    >
                        {{ productCategory.name }}
                    </span>
                </div>
                <h1 class="product-title">{{ product.name }}</h1>
                <div class="product-price">
                    {{ product.price?.toLocaleString('ru-RU') }} ₽
                </div>
            </div>

            <!-- Основное содержание -->
            <div class="product-content">
                <!-- Описание -->
                <div v-if="product.description" class="product-description">
                    <h3 class="section-title">
                        Описание
                    </h3>
                    <div class="description-content">
                        {{ product.description }}
                    </div>
                </div>

                <!-- Детали -->
                <div class="product-details">
                    <h3 class="section-title">
                        Детали товара
                    </h3>

                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Категория:</span>
                            <span class="detail-value">
                                {{ productCategory?.name || 'Не указана' }}
                            </span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Цена:</span>
                            <span class="detail-value price">
                                {{ product.price?.toLocaleString('ru-RU') }} ₽
                            </span>
                        </div>

                        <div class="detail-item" v-if="product.created_at">
                            <span class="detail-label">Добавлен:</span>
                            <span class="detail-value">
                                {{ new Date(product.created_at).toLocaleDateString('ru-RU') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Действия -->
            <div class="product-actions">
                <div class="actions-left">
                    <a href="/" class="btn btn-secondary">
                        ← Вернуться в каталог
                    </a>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Контейнер */
.product-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    margin-bottom: 2rem;
}

/* Хлебные крошки */
.breadcrumbs {
    margin-bottom: 1.5rem;
}

.breadcrumb-link {
    color: #764ba2;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s;
}

.breadcrumb-link:hover {
    background: #e9ecef;
    text-decoration: none;
}

/* Заголовок товара */
.product-header {
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    position: relative;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.product-id {
    font-size: 0.9rem;
    opacity: 0.9;
    background: rgba(255, 255, 255, 0.1);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
}

.product-category-tag {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

.product-title {
    font-size: 2.2rem;
    margin: 0 0 1rem 0;
    font-weight: 700;
    line-height: 1.2;
}

.product-price {
    font-size: 2rem;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.15);
    display: inline-block;
    padding: 0.5rem 1.5rem;
    border-radius: 12px;
    backdrop-filter: blur(10px);
}

/* Основное содержание */
.product-content {
    padding: 2rem;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #333;
    margin: 0 0 1.5rem 0;
    font-size: 1.3rem;
}

.section-title .icon {
    font-size: 1.2rem;
}

/* Описание */
.product-description {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.description-content {
    font-size: 1.1rem;
    line-height: 1.7;
    color: #444;
    white-space: pre-line;
}

/* Детали товара */
.product-details {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 12px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-label {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
}

.detail-value {
    font-size: 1.1rem;
    color: #333;
    font-weight: 600;
}

.detail-value.price {
    color: #4CAF50;
    font-size: 1.2rem;
}

.category-card h4 {
    margin: 0 0 1rem 0;
    font-size: 1.3rem;
}

/* Действия */
.product-actions {
    padding: 1.5rem 2rem;
    background: #f8f9fa;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.actions-left {
    display: flex;
    gap: 1rem;
}

/* Кнопки */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-secondary {
    background: white;
    color: #333;
    border: 2px solid #ddd;
}

.btn-secondary:hover {
    border-color: #764ba2;
    color: #764ba2;
}

/* Состояния загрузки и ошибки */
.loading-state,
.error-state {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #764ba2;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1.5rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.error-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #f44336;
}

.error-state h3 {
    color: #333;
    margin: 0 0 0.5rem 0;
}

.error-state p {
    color: #666;
    margin-bottom: 1.5rem;
}

/* Адаптивность */
@media (max-width: 768px) {
    .product-card {
        margin: 0 -1rem;
        border-radius: 0;
    }

    .product-header,
    .product-content,
    .product-actions {
        padding: 1.5rem;
    }

    .product-title {
        font-size: 1.8rem;
    }

    .product-price {
        font-size: 1.6rem;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .product-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .actions-left {
        width: 100%;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .product-title {
        font-size: 1.5rem;
    }

    .product-price {
        font-size: 1.3rem;
        padding: 0.4rem 1rem;
    }

    .section-title {
        font-size: 1.1rem;
    }
}
</style>
