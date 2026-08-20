<script setup>
defineProps({
    shown: { type: Number, required: true },
    total: { type: Number, required: true },
    sorts: { type: Array, required: true },
})

const search = defineModel('search', { type: String, default: '' })
const sort = defineModel('sort', { type: String, default: 'popular' })
const view = defineModel('view', { type: String, default: 'grid' })
const inStockOnly = defineModel('inStockOnly', { type: Boolean, default: false })
</script>

<template>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <label class="relative block min-w-0 flex-1">
            <span class="sr-only">Поиск по каталогу</span>
            <input
                v-model="search"
                type="search"
                placeholder="Название или автор…"
                class="w-full rounded-xl border border-slate-800 bg-slate-900/80 py-2.5 pl-10 pr-3 text-sm text-slate-100 placeholder:text-slate-500 outline-none ring-sky-400/0 transition focus:border-slate-600 focus:ring-2 focus:ring-sky-400/40"
            />
            <svg
                class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <circle cx="11" cy="11" r="7" />
                <path d="M20 20l-3-3" />
            </svg>
        </label>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <p class="mr-auto text-sm text-slate-500 lg:mr-0">
                {{ shown }} из {{ total }}
            </p>

            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-800 bg-slate-900/80 px-3 py-2 text-sm text-slate-300">
                <input v-model="inStockOnly" type="checkbox" class="accent-sky-400" />
                В наличии
            </label>

            <select
                v-model="sort"
                class="rounded-xl border border-slate-800 bg-slate-900/80 px-3 py-2 text-sm text-slate-200 outline-none focus:ring-2 focus:ring-sky-400/40"
                aria-label="Сортировка"
            >
                <option v-for="item in sorts" :key="item.id" :value="item.id">
                    {{ item.label }}
                </option>
            </select>

            <div class="flex rounded-xl border border-slate-800 bg-slate-900/80 p-0.5" role="group" aria-label="Вид">
                <button
                    type="button"
                    class="rounded-lg px-2.5 py-1.5 text-sm transition"
                    :class="view === 'grid' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-slate-200'"
                    :aria-pressed="view === 'grid'"
                    @click="view = 'grid'"
                >
                    Сетка
                </button>
                <button
                    type="button"
                    class="rounded-lg px-2.5 py-1.5 text-sm transition"
                    :class="view === 'list' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-slate-200'"
                    :aria-pressed="view === 'list'"
                    @click="view = 'list'"
                >
                    Список
                </button>
            </div>
        </div>
    </div>
</template>
