<script setup>
import BookCard from './BookCard.vue'
import BooksToolbar from './BooksToolbar.vue'
import { useBooksCatalog } from '../composables/useBooksCatalog'

const {
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
    total,
    shown,
    reset,
    genres,
    sorts,
} = useBooksCatalog()

const pages = (count) => Array.from({ length: count }, (_, i) => i + 1)
</script>

<template>
    <section>
        <header class="mb-6">
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-sky-400">Bookstore</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight">Каталог</h1>
            <p class="mt-1 max-w-xl text-sm text-slate-400">
                Инженерные книги: от Clean Code до SRE. Фильтры синкаются в URL — можно скинуть ссылку на выдачу.
            </p>
        </header>

        <BooksToolbar
            v-model:search="searchInput"
            v-model:sort="sort"
            v-model:view="view"
            v-model:inStockOnly="inStockOnly"
            :shown="shown"
            :total="total"
            :sorts="sorts"
        />

        <div class="mt-4 flex gap-2 overflow-x-auto pb-1">
            <button
                v-for="item in genres"
                :key="item.id"
                type="button"
                class="shrink-0 rounded-full border px-3 py-1.5 text-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400"
                :class="
                    genre === item.id
                        ? 'border-sky-400/60 bg-sky-400/10 text-sky-300'
                        : 'border-slate-800 bg-slate-900/50 text-slate-400 hover:border-slate-600 hover:text-slate-200'
                "
                :aria-pressed="genre === item.id"
                @click="genre = item.id"
            >
                {{ item.label }}
            </button>
        </div>

        <div
            v-if="error && !loading"
            class="mt-10 rounded-2xl border border-dashed border-rose-500/30 bg-slate-900/40 px-6 py-16 text-center"
        >
            <p class="text-lg font-medium text-slate-200">{{ error }}</p>
            <button
                type="button"
                class="mt-4 rounded-lg bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400"
                @click="load"
            >
                Повторить
            </button>
        </div>

        <div v-else-if="loading" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="n in 9"
                :key="n"
                class="animate-pulse overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/40"
            >
                <div class="aspect-[2/3] bg-slate-800/80" />
                <div class="space-y-2 p-4">
                    <div class="h-3 w-24 rounded bg-slate-800" />
                    <div class="h-4 w-3/4 rounded bg-slate-800" />
                    <div class="h-3 w-1/2 rounded bg-slate-800" />
                    <div class="mt-4 h-8 w-full rounded bg-slate-800" />
                </div>
            </div>
        </div>

        <div
            v-else-if="items.length"
            class="mt-6"
            :class="
                view === 'grid'
                    ? 'grid gap-4 sm:grid-cols-2 lg:grid-cols-3'
                    : 'flex flex-col gap-3'
            "
        >
            <BookCard v-for="book in items" :key="book.id" :book="book" :layout="view" />
        </div>

        <div
            v-else
            class="mt-10 rounded-2xl border border-dashed border-slate-700 bg-slate-900/40 px-6 py-16 text-center"
        >
            <p class="text-lg font-medium text-slate-200">Ничего не нашлось</p>
            <p class="mt-1 text-sm text-slate-500">Сбрось поиск или жанр — в каталоге {{ total }} книг.</p>
            <button
                type="button"
                class="mt-4 rounded-lg bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400"
                @click="reset"
            >
                Сбросить фильтры
            </button>
        </div>

        <nav
            v-if="!loading && pageCount > 1 && items.length"
            class="mt-8 flex justify-center gap-1"
            aria-label="Страницы"
        >
            <button
                v-for="n in pages(pageCount)"
                :key="n"
                type="button"
                class="min-w-9 rounded-lg px-3 py-1.5 text-sm transition"
                :class="n === page ? 'bg-sky-500 font-medium text-slate-950' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
                :aria-current="n === page ? 'page' : undefined"
                @click="page = n"
            >
                {{ n }}
            </button>
        </nav>
    </section>
</template>
