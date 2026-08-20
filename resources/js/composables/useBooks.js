import { ref } from 'vue'
import { api } from '../api/http'
import { catalog } from '../stores/catalog'
import { useCart } from './useCart'

const loading = ref(true)
const error = ref('')
let inflight = null
let loaded = false

export function useBooks() {
    const { remove: removeFromCart } = useCart()

    async function load() {
        if (loaded) return
        if (inflight) return inflight

        loading.value = true
        error.value = ''
        inflight = api
            .get('/api/books')
            .then(({ data }) => {
                catalog.value = data
                loaded = true
            })
            .catch(() => {
                catalog.value = []
                error.value = 'Не удалось загрузить каталог'
            })
            .finally(() => {
                inflight = null
                loading.value = false
            })

        return inflight
    }

    async function create(payload) {
        const { data } = await api.post('/api/books', payload)
        catalog.value = [...catalog.value, data]
        return data
    }

    async function remove(id) {
        await api.delete(`/api/books/${id}`)
        catalog.value = catalog.value.filter((book) => book.id !== id)
        removeFromCart(id)
    }

    function getById(id) {
        return catalog.value.find((book) => book.id === Number(id)) ?? null
    }

    function relatedOf(book, limit = 3) {
        if (!book) return []
        return catalog.value.filter((item) => item.genre === book.genre && item.id !== book.id).slice(0, limit)
    }

    return { catalog, loading, error, load, create, remove, getById, relatedOf }
}
