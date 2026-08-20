import { computed, reactive } from 'vue'
import { api, TOKEN_KEY, USER_KEY, onUnauthorized } from '../api/http'

function loadUser() {
    try {
        return JSON.parse(localStorage.getItem(USER_KEY) || 'null')
    } catch {
        return null
    }
}

const state = reactive({
    token: localStorage.getItem(TOKEN_KEY),
    user: loadUser(),
    loading: false,
    error: '',
})

function persist(token, user) {
    state.token = token
    state.user = user
    if (token) localStorage.setItem(TOKEN_KEY, token)
    else localStorage.removeItem(TOKEN_KEY)
    if (user) localStorage.setItem(USER_KEY, JSON.stringify(user))
    else localStorage.removeItem(USER_KEY)
}

export function useAuth() {
    const isAuthenticated = computed(() => Boolean(state.token && state.user))

    async function login(email, password) {
        state.loading = true
        state.error = ''
        try {
            const { data } = await api.post('/api/login', { email, password })
            persist(data.access_token, data.user)
        } catch (err) {
            const payload = err.response?.data
            state.error = payload?.errors?.email?.[0] || payload?.message || 'Не удалось войти'
            throw err
        } finally {
            state.loading = false
        }
    }

    async function logout() {
        try {
            await api.post('/api/logout')
        } catch {
            // token already dead — всё равно чистим клиент
        }
        persist(null, null)
    }

    async function hydrate() {
        if (!state.token) return
        try {
            const { data } = await api.get('/api/me')
            persist(state.token, data)
        } catch {
            persist(null, null)
        }
    }

    return {
        user: computed(() => state.user),
        token: computed(() => state.token),
        loading: computed(() => state.loading),
        error: computed(() => state.error),
        isAuthenticated,
        login,
        logout,
        hydrate,
    }
}

onUnauthorized(() => persist(null, null))
