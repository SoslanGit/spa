<script setup>
import { computed, onUnmounted, ref } from 'vue'
import BookCover from './BookCover.vue'
import { useAuth } from '../composables/useAuth'
import { useBooks } from '../composables/useBooks'
import { useCart } from '../composables/useCart'
import { genreLabel } from '../data/books'
import { discountPercent, formatPrice, formatReviews } from '../utils/format'

const props = defineProps({
    book: { type: Object, required: true },
    layout: { type: String, default: 'grid' },
})

const { add, qtyOf } = useCart()
const { isAuthenticated, user } = useAuth()
const { remove } = useBooks()
const qty = qtyOf(props.book.id)
const addedFlash = ref(false)
const removing = ref(false)
const deleteError = ref('')
let flashTimer

onUnmounted(() => clearTimeout(flashTimer))

const canDelete = computed(
    () => isAuthenticated.value && Number(props.book.userId) === Number(user.value?.id),
)

const badge = computed(() => {
    if (props.book.badge === 'hit') return { text: 'Хит', class: 'bg-amber-400 text-amber-950' }
    if (props.book.badge === 'new') return { text: 'New', class: 'bg-sky-400 text-sky-950' }
    if (props.book.badge === 'sale' || discountPercent(props.book)) {
        return { text: `−${discountPercent(props.book)}%`, class: 'bg-rose-400 text-rose-950' }
    }
    return null
})

function onAdd() {
    add(props.book)
    addedFlash.value = true
    clearTimeout(flashTimer)
    flashTimer = setTimeout(() => {
        addedFlash.value = false
    }, 1100)
}

async function onDelete() {
    if (!confirm(`Удалить «${props.book.title}»?`)) return
    removing.value = true
    deleteError.value = ''
    try {
        await remove(props.book.id)
    } catch (err) {
        deleteError.value = err.response?.data?.message || 'Нельзя удалить'
    } finally {
        removing.value = false
    }
}
</script>

<template>
    <article
        class="group relative flex overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60 transition hover:border-slate-600 hover:bg-slate-900"
        :class="layout === 'list' ? 'flex-row gap-4 p-3 sm:gap-5 sm:p-4' : 'flex-col'"
    >
        <router-link
            :to="{ name: 'book-detail', params: { id: book.id } }"
            class="relative shrink-0"
            :class="layout === 'list' ? 'w-24 sm:w-32' : 'block'"
        >
            <BookCover :book="book" :compact="layout === 'list'" />
            <span
                v-if="badge"
                class="absolute right-2 top-2 rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                :class="badge.class"
            >
                {{ badge.text }}
            </span>
            <span
                v-if="!book.inStock"
                class="absolute inset-x-2 bottom-2 rounded-md bg-slate-950/80 px-2 py-1 text-center text-[10px] font-medium uppercase tracking-wide text-slate-300"
            >
                Нет в наличии
            </span>
        </router-link>

        <div class="flex min-w-0 flex-1 flex-col" :class="layout === 'grid' ? 'p-4 pt-3' : 'py-0.5'">
            <p class="text-[11px] uppercase tracking-wider text-slate-500">
                {{ genreLabel(book.genre) }} · {{ book.year }}
            </p>
            <router-link
                :to="{ name: 'book-detail', params: { id: book.id } }"
                class="mt-0.5 line-clamp-2 font-semibold text-slate-100 transition group-hover:text-sky-300"
            >
                {{ book.title }}
            </router-link>
            <p class="mt-0.5 truncate text-sm text-slate-400">{{ book.author }}</p>

            <p v-if="layout === 'list'" class="mt-2 line-clamp-2 hidden text-sm text-slate-500 sm:block">
                {{ book.description }}
            </p>

            <div class="mt-2 flex items-center gap-2 text-sm">
                <span class="text-amber-400" aria-hidden="true">★</span>
                <span class="font-medium text-slate-200">{{ Number(book.rating).toFixed(1) }}</span>
                <span class="text-slate-500">{{ formatReviews(book.reviews) }} отзывов</span>
            </div>

            <div class="mt-auto flex items-end justify-between gap-3 pt-3">
                <div>
                    <p class="text-lg font-semibold tracking-tight">{{ formatPrice(book.price) }}</p>
                    <p v-if="book.oldPrice" class="text-xs text-slate-500 line-through">
                        {{ formatPrice(book.oldPrice) }}
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <button
                        v-if="canDelete"
                        type="button"
                        class="rounded-lg border border-rose-500/30 px-3 py-1.5 text-sm font-medium text-rose-300 transition hover:border-rose-400 hover:bg-rose-500/10 disabled:opacity-40"
                        :disabled="removing"
                        :title="deleteError || undefined"
                        @click.stop="onDelete"
                    >
                        {{ removing ? '…' : 'Удалить' }}
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400 disabled:cursor-not-allowed disabled:opacity-40"
                        :class="
                            addedFlash
                                ? 'bg-emerald-500 text-emerald-950'
                                : qty
                                  ? 'bg-slate-800 text-sky-300 ring-1 ring-slate-700 hover:bg-slate-700'
                                  : 'bg-sky-500 text-slate-950 hover:bg-sky-400'
                        "
                        :disabled="!book.inStock"
                        @click="onAdd"
                    >
                        <span v-if="addedFlash">Добавлено</span>
                        <span v-else-if="qty">В корзине · {{ qty }}</span>
                        <span v-else>В корзину</span>
                    </button>
                </div>
            </div>
        </div>
    </article>
</template>
