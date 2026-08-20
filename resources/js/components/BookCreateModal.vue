<script setup>
import { onMounted, onUnmounted, watch } from 'vue'
import BookCreateForm from './BookCreateForm.vue'
import { useCreateBookModal } from '../composables/useCreateBookModal'

const { open, hide } = useCreateBookModal()

function onKey(event) {
    if (event.key === 'Escape') hide()
}

watch(open, (value) => {
    document.body.style.overflow = value ? 'hidden' : ''
})

onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => {
    window.removeEventListener('keydown', onKey)
    document.body.style.overflow = ''
})
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-950/70 p-4 pt-[10vh] backdrop-blur-sm"
            @click.self="hide"
        >
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="create-book-title"
                class="w-full max-w-lg rounded-2xl border border-slate-700 bg-slate-900 p-5 shadow-2xl"
            >
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 id="create-book-title" class="text-lg font-semibold text-sky-400">Новая книга</h2>
                    <button
                        type="button"
                        class="rounded-lg px-2 py-1 text-slate-400 hover:bg-slate-800 hover:text-white"
                        aria-label="Закрыть"
                        @click="hide"
                    >
                        ✕
                    </button>
                </div>
                <BookCreateForm @created="hide" />
            </div>
        </div>
    </Teleport>
</template>
