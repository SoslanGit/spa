<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;


class LogBookEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 30;
    public function __construct(
        public int $bookId,
        public string $event,
        public string $title,
        public array $changes = [],
    ) {
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Событие книги обработано через RabbitMQ', [
            'book_id' => $this->bookId,
            'event' => $this->event,
            'title' => $this->title,
            'changes' => $this->changes,
        ]);
    }

}
