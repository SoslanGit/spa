<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title',
    'author',
    'genre',
    'year',
    'pages',
    'price',
    'old_price',
    'rating',
    'reviews',
    'in_stock',
    'badge',
    'language',
    'description',
    'cover',
])]
class Book extends Model
{
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'pages' => 0,
        'rating' => 0,
        'reviews' => 0,
        'in_stock' => true,
        'language' => 'EN',
    ];

    public const GENRES = [
        'architecture',
        'engineering',
        'algorithms',
        'frontend',
        'backend',
        'devops',
        'career',
    ];

    public const COVERS = [
        ['from' => '#0f3d4c', 'to' => '#082f49', 'accent' => '#38bdf8'],
        ['from' => '#3f1d12', 'to' => '#1c1917', 'accent' => '#fb923c'],
        ['from' => '#1e1b4b', 'to' => '#0f172a', 'accent' => '#818cf8'],
        ['from' => '#14532d', 'to' => '#052e16', 'accent' => '#4ade80'],
        ['from' => '#7f1d1d', 'to' => '#1c1917', 'accent' => '#f87171'],
        ['from' => '#0c4a6e', 'to' => '#082f49', 'accent' => '#7dd3fc'],
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'year' => 'integer',
            'pages' => 'integer',
            'price' => 'integer',
            'old_price' => 'integer',
            'rating' => 'float',
            'reviews' => 'integer',
            'in_stock' => 'boolean',
            'cover' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function randomCover(): array
    {
        return self::COVERS[array_rand(self::COVERS)];
    }
}
