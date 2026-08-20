<script setup>
import BookCover from './BookCover.vue'
import { useBooks } from '../composables/useBooks'
import { useCart } from '../composables/useCart'
import { formatPrice } from '../utils/format'

const { lines, total, count, setQty, remove, clear } = useCart()
const { loading } = useBooks()
</script>

<template>
    <section>
        <header class="mb-6 flex items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">Корзина</h1>
                <p class="mt-1 text-sm text-slate-400">
                    {{ loading ? 'Загрузка…' : count ? `${count} шт. · ${formatPrice(total)}` : 'Пока пусто — загляни в каталог.' }}
                </p>
            </div>
            <button
                v-if="lines.length"
                type="button"
                class="text-sm text-slate-500 hover:text-rose-400"
                @click="clear"
            >
                Очистить
            </button>
        </header>

        <div v-if="loading" class="space-y-3">
            <div v-for="n in 3" :key="n" class="h-28 animate-pulse rounded-2xl border border-slate-800 bg-slate-900/40" />
        </div>

        <div v-else-if="!lines.length" class="rounded-2xl border border-dashed border-slate-700 px-6 py-16 text-center">
            <p class="text-lg font-medium">Корзина пуста</p>
            <router-link
                to="/"
                class="mt-4 inline-block rounded-lg bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400"
            >
                В каталог
            </router-link>
        </div>

        <ul v-else class="space-y-3">
            <li
                v-for="line in lines"
                :key="line.id"
                class="flex gap-4 rounded-2xl border border-slate-800 bg-slate-900/50 p-3"
            >
                <router-link
                    :to="{ name: 'book-detail', params: { id: line.book.id } }"
                    class="block w-24 shrink-0 sm:w-28"
                >
                    <BookCover :book="line.book" compact />
                </router-link>
                <div class="min-w-0 flex-1">
                    <router-link
                        :to="{ name: 'book-detail', params: { id: line.book.id } }"
                        class="font-medium hover:text-sky-300"
                    >
                        {{ line.book.title }}
                    </router-link>
                    <p class="truncate text-sm text-slate-500">{{ line.book.author }}</p>
                    <p class="mt-1 text-sm font-medium">{{ formatPrice(line.book.price) }}</p>
                </div>
                <div class="flex flex-col items-end justify-between">
                    <div class="flex items-center rounded-lg border border-slate-700">
                        <button type="button" class="px-2.5 py-1 text-slate-400 hover:text-white" @click="setQty(line.id, line.qty - 1)">
                            −
                        </button>
                        <span class="min-w-6 text-center text-sm">{{ line.qty }}</span>
                        <button type="button" class="px-2.5 py-1 text-slate-400 hover:text-white" @click="setQty(line.id, line.qty + 1)">
                            +
                        </button>
                    </div>
                    <button type="button" class="text-xs text-slate-500 hover:text-rose-400" @click="remove(line.id)">
                        Убрать
                    </button>
                </div>
            </li>
        </ul>

        <div v-if="lines.length" class="mt-8 flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-900 px-5 py-4">
            <p class="text-slate-400">Итого</p>
            <p class="text-2xl font-semibold">{{ formatPrice(total) }}</p>
        </div>
    </section>
</template>
