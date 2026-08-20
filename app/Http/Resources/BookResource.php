<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Book */
class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'title' => $this->title,
            'author' => $this->author,
            'genre' => $this->genre,
            'year' => $this->year,
            'pages' => $this->pages,
            'price' => $this->price,
            'oldPrice' => $this->old_price,
            'rating' => $this->rating,
            'reviews' => $this->reviews,
            'inStock' => $this->in_stock,
            'badge' => $this->badge,
            'language' => $this->language,
            'description' => $this->description,
            'cover' => $this->cover,
        ];
    }
}
