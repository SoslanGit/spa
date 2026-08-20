import { computed, reactive } from 'vue'
import { catalog } from '../stores/catalog'

const STORAGE_KEY = 'bookstore.cart'

function load() {
    try {
        const raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
        if (!Array.isArray(raw)) return []
        return raw.filter((line) => line && Number.isFinite(line.id) && line.qty > 0)
    } catch {
        return []
    }
}

const state = reactive({
    lines: load(),
})

function persist() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(state.lines))
}

export function useCart() {
    const lines = computed(() =>
        state.lines
            .map((line) => {
                const book = catalog.value.find((item) => item.id === line.id)
                return book ? { ...line, book } : null
            })
            .filter(Boolean),
    )

    const count = computed(() => lines.value.reduce((sum, line) => sum + line.qty, 0))
    const total = computed(() => lines.value.reduce((sum, line) => sum + line.book.price * line.qty, 0))

    function add(book, qty = 1) {
        const line = state.lines.find((item) => item.id === book.id)
        if (line) line.qty += qty
        else state.lines.push({ id: book.id, qty })
        persist()
    }

    function setQty(id, qty) {
        const line = state.lines.find((item) => item.id === id)
        if (!line) return
        if (qty <= 0) {
            remove(id)
            return
        }
        line.qty = qty
        persist()
    }

    function remove(id) {
        const index = state.lines.findIndex((item) => item.id === id)
        if (index !== -1) state.lines.splice(index, 1)
        persist()
    }

    function qtyOf(id) {
        return computed(() => state.lines.find((item) => item.id === id)?.qty ?? 0)
    }

    function clear() {
        state.lines.splice(0)
        persist()
    }

    return { lines, count, total, add, setQty, remove, qtyOf, clear }
}
