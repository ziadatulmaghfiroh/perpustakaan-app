<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 5 penulis, masing-masing 10 buku
        Author::factory()->count(5)->hasBooks(10)->create();

        // 8 kategori
        $categories = Category::factory()->count(8)->create();

        // kategori acak untuk setiap buku
        Book::all()->each(function ($book) use ($categories) {
            $book->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        });

        // pastikan ada buku dengan stok < 5 untuk uji fitur stok menipis
        Book::inRandomOrder()->limit(5)->update(['stock' => 2]);
    }
}