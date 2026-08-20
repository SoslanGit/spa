<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Book $book): bool
    {
        return (int) $book->user_id === (int) $user->id;
    }
}
