let currentToken = localStorage.getItem('token')

export const getToken = () => currentToken

export const setToken = (token) => {
    currentToken = token
    if (token) {
        localStorage.setItem('token', token)
    } else {
        localStorage.removeItem('token')
    }
}
