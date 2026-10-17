<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // GET /api/books?search=...&category_id=...&in_stock=1
    public function index(Request $request)
    {
        $books = Book::with(['author', 'categories'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($q) use ($s) {
                    $q->where('title', 'like', "%{$s}%")
                      ->orWhereHas('author', fn ($a) => $a->where('name', 'like', "%{$s}%"));
                });
            })
            ->when($request->filled('category_id'), function ($q) use ($request) {
                $q->whereHas('categories', fn ($c) => $c->where('categories.id', $request->category_id));
            })
            ->when($request->boolean('in_stock'), fn ($q) => $q->where('stock', '>', 0))
            ->latest()
            ->get();

        return response()->json($books);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'isbn'           => 'required|string|unique:books',
            'published_year' => 'required|integer|min:1900|max:' . date('Y'),
            'price'          => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'author_id'      => 'required|exists:authors,id',
            'category_ids'   => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $book = Book::create(collect($validated)->except('category_ids')->all());
        $book->categories()->sync($validated['category_ids']);

        return response()->json($book->load('author', 'categories'), 201);
    }

    public function show(Book $book)
    {
        return response()->json($book->load('author', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title'          => 'sometimes|string|max:255',
            'isbn'           => 'sometimes|string|unique:books,isbn,' . $book->id,
            'published_year' => 'sometimes|integer|min:1900|max:' . date('Y'),
            'price'          => 'sometimes|numeric|min:0',
            'stock'          => 'sometimes|integer|min:0',
            'author_id'      => 'sometimes|exists:authors,id',
            'category_ids'   => 'sometimes|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $book->update(collect($validated)->except('category_ids')->all());

        if (isset($validated['category_ids'])) {
            $book->categories()->sync($validated['category_ids']);
        }

        return response()->json($book->load('author', 'categories'));
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(['message' => 'Buku berhasil dihapus']);
    }

    // GET /api/books/low-stock  (stok < 5)
    public function lowStock()
    {
        $books = Book::with(['author', 'categories'])
            ->where('stock', '<', 5)
            ->orderBy('stock')
            ->get();

        return response()->json($books);
    }

    // GET /api/statistics
    public function statistics()
    {
        return response()->json([
            'total_books'      => Book::count(),
            'total_authors'    => Author::count(),
            'total_categories' => Category::count(),
        ]);
    }
}