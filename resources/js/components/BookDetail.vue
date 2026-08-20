<script setup>
import { computed, onMounted } from 'vue'
import BookCover from './BookCover.vue'
import BookCard from './BookCard.vue'
import { useBooks } from '../composables/useBooks'
import { useCart } from '../composables/useCart'
import { genreLabel } from '../data/books'
import { discountPercent, formatPrice, formatReviews } from '../utils/format'

const props = defineProps({
    id: { type: [String, Number], required: true },
})

const { add, lines } = useCart()
const { load, loading, error, getById, relatedOf } = useBooks()

onMounted(load)

const book = computed(() => getById(props.id))
const related = computed(() => relatedOf(book.value))
const qty = computed(() => lines.value.find((line) => line.id === book.value?.id)?.qty ?? 0)
const discount = computed(() => (book.value ? discountPercent(book.value) : 0))
</script>

<template>
    <div v-if="loading && !book" class="mx-auto max-w-sm animate-pulse space-y-3 py-16">
        <div class="h-4 w-24 rounded bg-slate-800" />
        <div class="h-8 w-full rounded bg-slate-800" />
        <div class="h-4 w-2/3 rounded bg-slate-800" />
    </div>

    <div v-else-if="error && !book" class="py-16 text-center">
        <h1 class="text-2xl font-semibold">Не удалось загрузить</h1>
        <p class="mt-2 text-slate-400">{{ error }}</p>
        <button type="button" class="mt-4 text-sky-400 hover:underline" @click="load">Повторить</button>
    </div>

    <div v-else-if="!book" class="py-16 text-center">
        <h1 class="text-2xl font-semibold">Книга не найдена</h1>
        <p class="mt-2 text-slate-400">В каталоге нет id {{ id }}.</p>
        <router-link to="/" class="mt-4 inline-block text-sky-400 hover:underline">Назад в каталог</router-link>
    </div>

    <article v-else>
        <router-link to="/" class="text-sm text-slate-400 transition hover:text-sky-400">← Каталог</router-link>

        <div class="mt-6 grid gap-8 lg:grid-cols-[240px_1fr]">
            <div class="mx-auto w-56 lg:mx-0 lg:w-full">
                <BookCover :book="book" />
            </div>

            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-sky-400">{{ genreLabel(book.genre) }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ book.title }}</h1>
                <p class="mt-1 text-slate-400">{{ book.author }}</p>

                <div class="mt-4 flex flex-wrap items-center gap-3 text-sm">
                    <span class="text-amber-400">★ {{ Number(book.rating).toFixed(1) }}</span>
                    <span class="text-slate-500">{{ formatReviews(book.reviews) }} отзывов</span>
                    <span
                        class="rounded-full px-2 py-0.5 text-xs"
                        :class="book.inStock ? 'bg-emerald-400/10 text-emerald-300' : 'bg-slate-800 text-slate-400'"
                    >
                        {{ book.inStock ? 'В наличии' : 'Нет в наличии' }}
                    </span>
                </div>

                <p class="mt-5 max-w-2xl leading-relaxed text-slate-300">{{ book.description }}</p>

                <dl class="mt-6 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-3">
                        <dt class="text-slate-500">Год</dt>
                        <dd class="mt-0.5 font-medium">{{ book.year }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-3">
                        <dt class="text-slate-500">Страниц</dt>
                        <dd class="mt-0.5 font-medium">{{ book.pages }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-3">
                        <dt class="text-slate-500">Язык</dt>
                        <dd class="mt-0.5 font-medium">{{ book.language }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-3">
                        <dt class="text-slate-500">Жанр</dt>
                        <dd class="mt-0.5 font-medium">{{ genreLabel(book.genre) }}</dd>
                    </div>
                </dl>

                <div class="mt-8 flex flex-wrap items-end gap-4">
                    <div>
                        <p class="text-3xl font-semibold tracking-tight">{{ formatPrice(book.price) }}</p>
                        <p v-if="book.oldPrice" class="text-sm text-slate-500">
                            <span class="line-through">{{ formatPrice(book.oldPrice) }}</span>
                            <span v-if="discount" class="ml-2 text-rose-400">−{{ discount }}%</span>
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-xl bg-sky-500 px-5 py-2.5 font-medium text-slate-950 transition hover:bg-sky-400 disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="!book.inStock"
                        @click="add(book)"
                    >
                        {{ qty ? `В корзине · ${qty}` : 'В корзину' }}
                    </button>
                </div>
            </div>
        </div>

        <section v-if="related.length" class="mt-14">
            <h2 class="text-lg font-semibold">Ещё из раздела «{{ genreLabel(book.genre) }}»</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <BookCard v-for="item in related" :key="item.id" :book="item" />
            </div>
        </section>
    </article>
</template>
