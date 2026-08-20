<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('author');
            $table->string('genre');
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('pages')->default(0);
            $table->unsignedInteger('price');
            $table->unsignedInteger('old_price')->nullable();
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('reviews')->default(0);
            $table->boolean('in_stock')->default(true);
            $table->string('badge')->nullable();
            $table->string('language', 8)->default('EN');
            $table->text('description')->nullable();
            $table->json('cover');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
