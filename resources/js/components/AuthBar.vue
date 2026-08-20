<script setup>
import { ref } from 'vue'
import { useAuth } from '../composables/useAuth'

const { user, loading, error, isAuthenticated, login, logout } = useAuth()

const email = ref('demo@bookstore.test')
const password = ref('')

async function onSubmit() {
    try {
        await login(email.value, password.value)
        password.value = ''
    } catch {
        // error уже в state
    }
}
</script>

<template>
    <div class="min-w-0">
        <form
            v-if="!isAuthenticated"
            class="flex flex-wrap items-center justify-end gap-2"
            @submit.prevent="onSubmit"
        >
            <input
                v-model="email"
                type="email"
                required
                autocomplete="username"
                placeholder="Email"
                class="h-9 w-40 rounded-lg border border-slate-800 bg-slate-900 px-2.5 text-sm text-slate-100 outline-none placeholder:text-slate-500 focus:border-slate-600 focus:ring-2 focus:ring-sky-400/40"
            />
            <input
                v-model="password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Пароль"
                class="h-9 w-28 rounded-lg border border-slate-800 bg-slate-900 px-2.5 text-sm text-slate-100 outline-none placeholder:text-slate-500 focus:border-slate-600 focus:ring-2 focus:ring-sky-400/40"
            />
            <button
                type="submit"
                class="h-9 rounded-lg bg-sky-500 px-3 text-sm font-medium text-slate-950 transition hover:bg-sky-400 disabled:opacity-50"
                :disabled="loading"
            >
                {{ loading ? '…' : 'Войти' }}
            </button>
        </form>

        <div v-else class="flex items-center justify-end gap-3">
            <div class="hidden text-right sm:block">
                <p class="text-sm font-medium leading-tight text-slate-100">{{ user.name }}</p>
                <p class="text-[11px] text-slate-500">{{ user.email }}</p>
            </div>
            <button
                type="button"
                class="h-9 rounded-lg border border-slate-700 px-3 text-sm text-slate-300 transition hover:border-slate-500 hover:text-white"
                @click="logout"
            >
                Выйти
            </button>
        </div>

        <p v-if="error && !isAuthenticated" class="mt-1 text-right text-xs text-rose-400">{{ error }}</p>
    </div>
</template>
