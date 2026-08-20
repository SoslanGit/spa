<script setup>
import { computed } from 'vue'
import { genreLabel } from '../data/books'

const props = defineProps({
    book: { type: Object, required: true },
    compact: { type: Boolean, default: false },
})

const FALLBACK = { from: '#1e293b', to: '#0f172a', accent: '#38bdf8' }

const cover = computed(() => ({
    ...FALLBACK,
    ...(props.book.cover ?? {}),
}))

const style = computed(() => ({
    background: `linear-gradient(152deg, ${cover.value.from} 0%, ${cover.value.to} 100%)`,
}))
</script>

<template>
    <div
        class="relative aspect-[2/3] overflow-hidden rounded-lg shadow-inner ring-1 ring-white/10"
        :style="style"
    >
        <span
            class="absolute inset-y-0 left-0"
            :class="compact ? 'w-1' : 'w-1.5'"
            :style="{ background: cover.accent }"
        />
        <div
            class="pointer-events-none absolute -right-6 -top-10 size-28 rounded-full opacity-30 blur-2xl"
            :style="{ background: cover.accent }"
        />
        <div
            class="flex h-full flex-col justify-between text-white"
            :class="compact ? 'p-2' : 'p-3.5 sm:p-4'"
        >
            <p
                class="font-medium uppercase tracking-[0.18em] text-white/60"
                :class="compact ? 'text-[8px]' : 'text-[10px] font-medium tracking-[0.22em]'"
            >
                {{ genreLabel(book.genre) }}
            </p>
            <div>
                <p
                    class="font-semibold leading-snug"
                    :class="compact ? 'line-clamp-3 text-[11px]' : 'text-base'"
                >
                    {{ book.title }}
                </p>
                <p v-if="!compact" class="mt-1 line-clamp-2 text-xs text-white/55">{{ book.author }}</p>
            </div>
        </div>
    </div>
</template>
