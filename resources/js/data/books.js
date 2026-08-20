export const GENRES = [
    { id: 'all', label: 'Все' },
    { id: 'architecture', label: 'Архитектура' },
    { id: 'engineering', label: 'Инженерия' },
    { id: 'algorithms', label: 'Алгоритмы' },
    { id: 'frontend', label: 'Frontend' },
    { id: 'backend', label: 'Backend' },
    { id: 'devops', label: 'DevOps' },
    { id: 'career', label: 'Карьера' },
]

export const SORTS = [
    { id: 'popular', label: 'По популярности' },
    { id: 'price-asc', label: 'Сначала дешёвые' },
    { id: 'price-desc', label: 'Сначала дорогие' },
    { id: 'rating', label: 'По рейтингу' },
    { id: 'newest', label: 'Новинки' },
]

export const PAGE_SIZE = 9

export function genreLabel(id) {
    return GENRES.find((g) => g.id === id)?.label ?? id
}
