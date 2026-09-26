<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class BookCatalogCache
{
    private const INDEX_KEY = 'books:index:v2';

    private const TTL_MINUTES = 10;

    /**
     * @return Collection<int, Book>
     */
    public function getAll(): Collection
    {
        $rows = Cache::remember(
            self::INDEX_KEY,
            now()->addMinutes(self::TTL_MINUTES),
            static fn (): array => Book::query()
                ->orderBy('id')
                ->get()
                ->map(
                    static fn (Book $book): array => $book->getAttributes()
                )
                ->all(),
        );

        return Book::hydrate($rows);
    }

    public function forget(): void
    {
        Cache::forget(self::INDEX_KEY);
    }
}