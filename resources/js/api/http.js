import axios from 'axios'

export const TOKEN_KEY = 'bookstore.jwt'
export const USER_KEY = 'bookstore.user'

export const api = axios.create({
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
})

api.interceptors.request.use((config) => {
    const token = localStorage.getItem(TOKEN_KEY)
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

/** @type {(() => void) | null} */
let unauthorizedHandler = null

export function onUnauthorized(handler) {
    unauthorizedHandler = handler
}

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const url = String(error.config?.url ?? '')
        if (error.response?.status === 401 && !url.includes('/login')) {
            unauthorizedHandler?.()
        }
        return Promise.reject(error)
    },
)
