<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BookController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BookResource::collection(
            Book::query()->orderBy('id')->get(),
        );
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = $request->user()->books()->create($request->validated() + [
            'cover' => Book::randomCover(),
            'badge' => 'new',
        ]);

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateBookRequest $request, Book $book): BookResource
    {
        $book->update($request->validated());

        return new BookResource($book->refresh());
    }

    public function destroy(Book $book): Response
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->noContent();
    }
}
