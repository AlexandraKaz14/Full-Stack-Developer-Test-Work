import { ref, computed } from 'vue'
import api from './useApi'
import { setToken } from './useToken'

const token = ref(localStorage.getItem('token'))

export function useAuth() {
    const login = async (email, password) => {
        try {
            const response = await api.post('/login', { email, password }, {
                headers: {
                    Authorization: undefined
                }
            })

            const newToken = response.data.token || response.data.access_token

            if (!newToken) {
                throw new Error('Токен не получен от сервера')
            }

            setToken(newToken)
            token.value = newToken

            return response
        } catch (error) {
            console.error('Login error:', error)
            throw error
        }
    }

    const logout = () => {
        setToken(null)
        token.value = null
        location.href = '/';
    }

    const isAuthenticated = computed(() => !!token.value)

    return {
        token,
        login,
        logout,
        isAuthenticated,
    }
}

export const initializeAuth = () => {
    const savedToken = localStorage.getItem('token')
    if (savedToken) {
        setToken(savedToken)
        token.value = savedToken
    }
}
