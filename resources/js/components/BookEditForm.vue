<script setup>
import { reactive, ref, watch } from 'vue'
import { GENRES } from '../data/books'
import { useBooks } from '../composables/useBooks'

const props = defineProps({
    book: { type: Object, required: true },
})

const emit = defineEmits(['updated'])
const { update } = useBooks()

const form = reactive({
    title: '',
    author: '',
    genre: 'engineering',
    year: new Date().getFullYear(),
    pages: 300,
    price: 1990,
    description: '',
})

const submitting = ref(false)
const error = ref('')
const genres = GENRES.filter((g) => g.id !== 'all')
const fieldClass =
    'h-10 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 text-sm text-slate-100 caret-sky-400 placeholder:text-slate-500 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/40'

watch(
    () => props.book,
    (book) => {
        if (!book) return
        form.title = book.title ?? ''
        form.author = book.author ?? ''
        form.genre = book.genre ?? 'engineering'
        form.year = book.year ?? new Date().getFullYear()
        form.pages = book.pages ?? 300
        form.price = book.price ?? 0
        form.description = book.description ?? ''
        error.value = ''
    },
    { immediate: true },
)

async function onSubmit() {
    submitting.value = true
    error.value = ''

    try {
        await update(props.book.id, { ...form })
        emit('updated')
    } catch (err) {
        const payload = err.response?.data
        error.value = payload?.errors
            ? Object.values(payload.errors).flat()[0]
            : payload?.message || 'Не удалось обновить книгу'
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <form class="grid gap-3 sm:grid-cols-2" @submit.prevent="onSubmit">
        <label class="block">
            <span class="mb-1 block text-xs font-medium text-sky-300">Название</span>
            <input v-model="form.title" required autofocus :class="fieldClass" />
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-medium text-sky-300">Автор</span>
            <input v-model="form.author" required :class="fieldClass" />
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-medium text-sky-300">Жанр</span>
            <select v-model="form.genre" :class="fieldClass">
                <option v-for="item in genres" :key="item.id" :value="item.id" class="bg-slate-900 text-slate-100">
                    {{ item.label }}
                </option>
            </select>
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-medium text-sky-300">Цена ₽</span>
            <input v-model.number="form.price" type="number" min="0" required :class="fieldClass" />
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-medium text-sky-300">Год</span>
            <input v-model.number="form.year" type="number" min="1900" required :class="fieldClass" />
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-medium text-sky-300">Страниц</span>
            <input v-model.number="form.pages" type="number" min="1" :class="fieldClass" />
        </label>
        <label class="block sm:col-span-2">
            <span class="mb-1 block text-xs font-medium text-sky-300">Описание</span>
            <textarea
                v-model="form.description"
                rows="3"
                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 caret-sky-400 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/40"
            />
        </label>
        <div class="flex items-center justify-between gap-3 sm:col-span-2">
            <p v-if="error" class="text-sm text-rose-400">{{ error }}</p>
            <button
                type="submit"
                class="ml-auto h-10 rounded-lg bg-sky-500 px-4 text-sm font-medium text-slate-950 hover:bg-sky-400 disabled:opacity-50"
                :disabled="submitting"
            >
                {{ submitting ? 'Сохраняю…' : 'Сохранить' }}
            </button>
        </div>
    </form>
</template>
