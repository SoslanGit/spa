export function formatPrice(value) {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        maximumFractionDigits: 0,
    }).format(value)
}

export function discountPercent(book) {
    if (!book.oldPrice || book.oldPrice <= book.price) return 0
    return Math.round((1 - book.price / book.oldPrice) * 100)
}

export function formatReviews(n) {
    return new Intl.NumberFormat('ru-RU').format(n)
}
