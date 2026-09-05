import { ref } from 'vue'

const open = ref(false)
const book = ref(null)

export function useEditBookModal() {
    return {
        open,
        book,
        show: (value) => {
            book.value = value
            open.value = true
        },
        hide: () => {
            open.value = false
            book.value = null
        },
    }
}
