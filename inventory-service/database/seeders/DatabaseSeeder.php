<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            ['isbn' => '978-0141439518', 'title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'genre' => 'Classic', 'price' => 450, 'cost_price' => 280, 'stock_qty' => 25, 'reorder_level' => 5],
            ['isbn' => '978-0743273565', 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'genre' => 'Classic', 'price' => 380, 'cost_price' => 220, 'stock_qty' => 18, 'reorder_level' => 5],
            ['isbn' => '978-0439023528', 'title' => 'The Hunger Games', 'author' => 'Suzanne Collins', 'genre' => 'Young Adult', 'price' => 520, 'cost_price' => 310, 'stock_qty' => 40, 'reorder_level' => 10],
            ['isbn' => '978-0316769488', 'title' => 'The Catcher in the Rye', 'author' => 'J.D. Salinger', 'genre' => 'Classic', 'price' => 420, 'cost_price' => 250, 'stock_qty' => 3, 'reorder_level' => 8],
            ['isbn' => '978-0061120084', 'title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'genre' => 'Classic', 'price' => 490, 'cost_price' => 290, 'stock_qty' => 22, 'reorder_level' => 6],
            ['isbn' => '978-0593099322', 'title' => 'Dune', 'author' => 'Frank Herbert', 'genre' => 'Science Fiction', 'price' => 650, 'cost_price' => 380, 'stock_qty' => 15, 'reorder_level' => 5],
        ];

        foreach ($books as $book) {
            Book::updateOrCreate(['isbn' => $book['isbn']], $book);
        }
    }
}
