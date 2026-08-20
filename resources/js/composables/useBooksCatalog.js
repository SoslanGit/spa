import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { GENRES, PAGE_SIZE, SORTS } from '../data/books'
import { useBooks } from './useBooks'

export function useBooksCatalog() {
    const route = useRoute()
    const router = useRouter()
    const { catalog, loading, error, load } = useBooks()

    const searchInput = ref(String(route.query.q ?? ''))

    const genre = computed({
        get: () => String(route.query.genre ?? 'all'),
        set: (value) => patchQuery({ genre: value === 'all' ? undefined : value, page: undefined }),
    })

    const sort = computed({
        get: () => String(route.query.sort ?? 'popular'),
        set: (value) => patchQuery({ sort: value === 'popular' ? undefined : value, page: undefined }),
    })

    const view = computed({
        get: () => (route.query.view === 'list' ? 'list' : 'grid'),
        set: (value) => patchQuery({ view: value === 'grid' ? undefined : value }),
    })

    const inStockOnly = computed({
        get: () => route.query.stock === '1',
        set: (value) => patchQuery({ stock: value ? '1' : undefined, page: undefined }),
    })

    const page = computed({
        get: () => Math.max(1, Number(route.query.page) || 1),
        set: (value) => patchQuery({ page: value <= 1 ? undefined : String(value) }),
    })

    function patchQuery(patch) {
        const query = { ...route.query }
        for (const [key, value] of Object.entries(patch)) {
            if (value == null || value === '') delete query[key]
            else query[key] = value
        }
        router.replace({ query })
    }

    let debounce
    watch(searchInput, (value) => {
        clearTimeout(debounce)
        debounce = setTimeout(() => {
            if (value === String(route.query.q ?? '')) return
            patchQuery({ q: value || undefined, page: undefined })
        }, 280)
    })

    onUnmounted(() => clearTimeout(debounce))

    watch(
        () => String(route.query.q ?? ''),
        (query) => {
            if (query !== searchInput.value) searchInput.value = query
        },
    )

    const filtered = computed(() => {
        const q = String(route.query.q ?? '').trim().toLowerCase()
        let rows = catalog.value.filter((book) => {
            if (genre.value !== 'all' && book.genre !== genre.value) return false
            if (inStockOnly.value && !book.inStock) return false
            if (q && !`${book.title} ${book.author}`.toLowerCase().includes(q)) return false
            return true
        })

        const score = (book) => book.rating * Math.log10(book.reviews + 1)

        switch (sort.value) {
            case 'price-asc':
                return [...rows].sort((a, b) => a.price - b.price)
            case 'price-desc':
                return [...rows].sort((a, b) => b.price - a.price)
            case 'rating':
                return [...rows].sort((a, b) => b.rating - a.rating || b.reviews - a.reviews)
            case 'newest':
                return [...rows].sort((a, b) => b.year - a.year)
            default:
                return [...rows].sort((a, b) => score(b) - score(a))
        }
    })

    const pageCount = computed(() => Math.max(1, Math.ceil(filtered.value.length / PAGE_SIZE) || 1))

    watch(pageCount, (count) => {
        if (page.value > count) page.value = count
    })

    const items = computed(() => {
        const start = (Math.min(page.value, pageCount.value) - 1) * PAGE_SIZE
        return filtered.value.slice(start, start + PAGE_SIZE)
    })

    function reset() {
        searchInput.value = ''
        router.replace({ query: {} })
    }

    onMounted(load)

    return {
        loading,
        error,
        load,
        searchInput,
        genre,
        sort,
        view,
        inStockOnly,
        page,
        pageCount,
        items,
        total: computed(() => catalog.value.length),
        shown: computed(() => filtered.value.length),
        reset,
        genres: GENRES,
        sorts: SORTS,
    }
}
