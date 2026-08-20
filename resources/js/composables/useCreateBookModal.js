import { ref } from 'vue'

const open = ref(false)

export function useCreateBookModal() {
    return {
        open,
        show: () => {
            open.value = true
        },
        hide: () => {
            open.value = false
        },
    }
}
