<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useAuth } from '@/composables/useAuth'

const email = ref('')
const password = ref('')
const error = ref(null)
const loading = ref(false)

const { login } = useAuth()

const submit = async () => {
    error.value = null
    loading.value = true

    try {
        await login(email.value, password.value)
        router.visit('/admin/products')
    } catch (err) {
        error.value = err.response?.data?.message || 'Неверные данные'
        console.error('Login error:', err)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="login-container">
        <div class="login-card">
            <h1 class="login-title">Вход в панель управления</h1>

            <div v-if="error" class="error-message">
                {{ error }}
            </div>

            <form @submit.prevent="submit" class="login-form">
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        class="form-input"
                        placeholder="admin@example.com"
                        required
                    />
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Пароль</label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        class="form-input"
                        placeholder="Пароль"
                        required
                    />
                </div>

                <button
                    type="submit"
                    class="login-button"
                    :disabled="loading"
                >
                    {{ loading ? 'Вход...' : 'Войти' }}
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped>
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
    padding: 2rem;
}

.login-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    padding: 2.5rem;
    width: 100%;
    max-width: 400px;
}

.login-title {
    color: #333;
    text-align: center;
    margin-bottom: 2rem;
    font-size: 1.5rem;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    margin-bottom: 0.5rem;
    color: #555;
    font-weight: 500;
}

.form-input {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-input:focus {
    outline: none;
    border-color: #764ba2;
    box-shadow: 0 0 0 2px rgba(118, 75, 162, 0.1);
}

.login-button {
    padding: 0.75rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
}

.login-button:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(118, 75, 162, 0.3);
}

.login-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.error-message {
    background: #ffebee;
    color: #c62828;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    text-align: center;
}

@media (max-width: 480px) {
    .login-card {
        padding: 1.5rem;
        margin: 0 1rem;
    }
}
</style>
