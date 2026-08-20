<script setup>
import { onMounted } from 'vue'
import AuthBar from './components/AuthBar.vue'
import BookCreateModal from './components/BookCreateModal.vue'
import { useAuth } from './composables/useAuth'
import { useBooks } from './composables/useBooks'
import { useCart } from './composables/useCart'
import { useCreateBookModal } from './composables/useCreateBookModal'

const { count } = useCart()
const { hydrate, isAuthenticated } = useAuth()
const { load } = useBooks()
const { show: showCreate } = useCreateBookModal()

onMounted(() => {
    hydrate()
    load()
})
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <nav class="sticky top-0 z-20 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-3">
                <div class="flex items-center gap-6">
                    <router-link to="/" class="text-sm font-semibold tracking-wide">
                        <span class="text-sky-400">Vue</span> Bookstore
                    </router-link>
                    <div class="flex items-center gap-5 text-sm">
                        <router-link
                            to="/"
                            class="text-slate-400 transition hover:text-sky-400 aria-[current=page]:text-sky-400"
                        >
                            Книги
                        </router-link>
                        <router-link
                            to="/cart"
                            class="relative text-slate-400 transition hover:text-sky-400 aria-[current=page]:text-sky-400"
                        >
                            Корзина
                            <span
                                v-if="count"
                                class="absolute -right-4 -top-2 min-w-5 rounded-full bg-sky-500 px-1 text-center text-[10px] font-bold leading-5 text-slate-950"
                            >
                                {{ count }}
                            </span>
                        </router-link>
                        <button
                            v-if="isAuthenticated"
                            type="button"
                            class="rounded-lg bg-sky-500 px-3 py-1.5 text-sm font-medium text-slate-950 hover:bg-sky-400"
                            @click="showCreate"
                        >
                            Добавить книгу
                        </button>
                    </div>
                </div>
                <AuthBar />
            </div>
        </nav>
        <BookCreateModal />
        <main class="mx-auto max-w-6xl p-6">
            <router-view />
        </main>
    </div>
</template>
