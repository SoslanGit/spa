import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import BooksList from './components/BooksList.vue'
import BookDetail from './components/BookDetail.vue'
import Cart from './components/Cart.vue'

const router = createRouter({
    history: createWebHistory(),
    scrollBehavior() {
        return { top: 0 }
    },
    routes: [
        { path: '/', component: BooksList, name: 'books' },
        { path: '/book/:id', component: BookDetail, name: 'book-detail', props: true },
        { path: '/cart', component: Cart, name: 'cart' },
    ],
})

const app = createApp(App)
app.use(router)
app.mount('#app')