<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        return response()->json(Author::withCount('books')->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:authors,email',
            'bio'   => 'nullable|string',
        ]);

        return response()->json(Author::create($validated), 201);
    }

    public function show(Author $author)
    {
        return response()->json($author->load('books'));
    }

    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:authors,email,' . $author->id,
            'bio'   => 'nullable|string',
        ]);

        $author->update($validated);

        return response()->json($author);
    }

    public function destroy(Request $request, Author $author)
    {
        $count = $author->books()->count();

        if (! $request->boolean('confirm')) {
            return response()->json([
                'message' => "Menghapus penulis ini akan ikut menghapus {$count} buku. Kirim ulang dengan ?confirm=true untuk melanjutkan.",
                'books_count' => $count,
            ], 409);
        }

        $author->delete();

        return response()->json([
            'message' => "Penulis dan {$count} buku terkait berhasil dihapus",
        ]);
    }
}