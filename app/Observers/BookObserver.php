<?php

namespace App\Observers;

use App\Models\Book;
use App\Services\BookCatalogCache;



class BookObserver
{
    public function saved(Book $book): void
    {
        app(BookCatalogCache::class)->forget();
    }

    public function deleted(Book $book): void
    {
        app(BookCatalogCache::class)->forget();
    }
}
