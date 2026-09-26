<?php

namespace App\Observers;

use App\Jobs\LogBookEvent;
use App\Models\Book;
use App\Services\BookCatalogCache;
use PhpParser\Node\Expr\Cast\Void_;
use Illuminate\Support\Facades\Log;



class BookObserver
{
    public function created(Book $book): void 
    {
      LogBookEvent::dispatch(
        bookId: $book->id,
        event: 'created',
        title: $book->title,
      )->onConnection('rabbitmq')->onQueue('books');
    }

    public function updated(Book $book): void
    {
        $changes = $book->getChanges();

        unset($changes['updated_at']);

        LogBookEvent::dispatch(
            bookId: $book->id,
            event: 'updated',
            title: $book->title,
            changes: $changes,
        )
            ->onConnection('rabbitmq')
            ->onQueue('books');
    }
    public function saved(Book $book): void
    {
        app(BookCatalogCache::class)->forget();
    }

    public function deleted(Book $book): void
    {
        app(BookCatalogCache::class)->forget();
    }
}
