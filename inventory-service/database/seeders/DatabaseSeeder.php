<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            // Fiction & Classics
            ['isbn' => '978-0141439518', 'title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'genre' => 'Classic', 'price' => 450, 'cost_price' => 280, 'stock_qty' => 25, 'reorder_level' => 5],
            ['isbn' => '978-0743273565', 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'genre' => 'Classic', 'price' => 380, 'cost_price' => 220, 'stock_qty' => 18, 'reorder_level' => 5],
            ['isbn' => '978-0316769488', 'title' => 'The Catcher in the Rye', 'author' => 'J.D. Salinger', 'genre' => 'Classic', 'price' => 420, 'cost_price' => 250, 'stock_qty' => 3, 'reorder_level' => 8], // Low stock!
            ['isbn' => '978-0061120084', 'title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'genre' => 'Classic', 'price' => 490, 'cost_price' => 290, 'stock_qty' => 22, 'reorder_level' => 6],
            ['isbn' => '978-0451524935', 'title' => '1984', 'author' => 'George Orwell', 'genre' => 'Sci-Fi', 'price' => 350, 'cost_price' => 200, 'stock_qty' => 50, 'reorder_level' => 10],
            ['isbn' => '978-0060850524', 'title' => 'Brave New World', 'author' => 'Aldous Huxley', 'genre' => 'Sci-Fi', 'price' => 400, 'cost_price' => 220, 'stock_qty' => 15, 'reorder_level' => 10],
            
            // Young Adult & Fantasy
            ['isbn' => '978-0439023528', 'title' => 'The Hunger Games', 'author' => 'Suzanne Collins', 'genre' => 'Young Adult', 'price' => 520, 'cost_price' => 310, 'stock_qty' => 40, 'reorder_level' => 10],
            ['isbn' => '978-0590353427', 'title' => 'Harry Potter and the Sorcerer\'s Stone', 'author' => 'J.K. Rowling', 'genre' => 'Fantasy', 'price' => 650, 'cost_price' => 400, 'stock_qty' => 120, 'reorder_level' => 20],
            ['isbn' => '978-0553103540', 'title' => 'A Game of Thrones', 'author' => 'George R.R. Martin', 'genre' => 'Fantasy', 'price' => 750, 'cost_price' => 500, 'stock_qty' => 0, 'reorder_level' => 15], // Out of stock!
            ['isbn' => '978-0345538376', 'title' => 'The Hobbit', 'author' => 'J.R.R. Tolkien', 'genre' => 'Fantasy', 'price' => 580, 'cost_price' => 320, 'stock_qty' => 35, 'reorder_level' => 10],
            ['isbn' => '978-0593099322', 'title' => 'Dune', 'author' => 'Frank Herbert', 'genre' => 'Sci-Fi', 'price' => 650, 'cost_price' => 380, 'stock_qty' => 15, 'reorder_level' => 5],
            ['isbn' => '978-1423140603', 'title' => 'The Lightning Thief', 'author' => 'Rick Riordan', 'genre' => 'Young Adult', 'price' => 450, 'cost_price' => 250, 'stock_qty' => 80, 'reorder_level' => 15],

            // Programming & Tech
            ['isbn' => '978-0132350884', 'title' => 'Clean Code', 'author' => 'Robert C. Martin', 'genre' => 'Technology', 'price' => 1800, 'cost_price' => 1200, 'stock_qty' => 8, 'reorder_level' => 10], // Low stock!
            ['isbn' => '978-0201616224', 'title' => 'The Pragmatic Programmer', 'author' => 'Andrew Hunt', 'genre' => 'Technology', 'price' => 2100, 'cost_price' => 1500, 'stock_qty' => 12, 'reorder_level' => 5],
            ['isbn' => '978-1491950296', 'title' => 'Programming Rust', 'author' => 'Jim Blandy', 'genre' => 'Technology', 'price' => 2400, 'cost_price' => 1800, 'stock_qty' => 5, 'reorder_level' => 5],
            ['isbn' => '978-1449331818', 'title' => 'Learning Python', 'author' => 'Mark Lutz', 'genre' => 'Technology', 'price' => 2600, 'cost_price' => 1900, 'stock_qty' => 2, 'reorder_level' => 5], // Super low!
            ['isbn' => '978-0134685991', 'title' => 'Effective Java', 'author' => 'Joshua Bloch', 'genre' => 'Technology', 'price' => 2200, 'cost_price' => 1600, 'stock_qty' => 18, 'reorder_level' => 5],

            // Business & Self-Help
            ['isbn' => '978-1847941831', 'title' => 'Atomic Habits', 'author' => 'James Clear', 'genre' => 'Self-Help', 'price' => 850, 'cost_price' => 400, 'stock_qty' => 200, 'reorder_level' => 30],
            ['isbn' => '978-0307465351', 'title' => 'The Lean Startup', 'author' => 'Eric Ries', 'genre' => 'Business', 'price' => 900, 'cost_price' => 550, 'stock_qty' => 45, 'reorder_level' => 15],
            ['isbn' => '978-1501111105', 'title' => 'Principles', 'author' => 'Ray Dalio', 'genre' => 'Business', 'price' => 1200, 'cost_price' => 800, 'stock_qty' => 0, 'reorder_level' => 10], // Out of stock!
            ['isbn' => '978-0062457714', 'title' => 'The Subtle Art of Not Giving a F*ck', 'author' => 'Mark Manson', 'genre' => 'Self-Help', 'price' => 750, 'cost_price' => 350, 'stock_qty' => 150, 'reorder_level' => 20],
            ['isbn' => '978-0671027032', 'title' => 'How to Win Friends', 'author' => 'Dale Carnegie', 'genre' => 'Self-Help', 'price' => 600, 'cost_price' => 300, 'stock_qty' => 60, 'reorder_level' => 15],

            // Mystery & Thriller
            ['isbn' => '978-0307588371', 'title' => 'Gone Girl', 'author' => 'Gillian Flynn', 'genre' => 'Mystery', 'price' => 550, 'cost_price' => 300, 'stock_qty' => 25, 'reorder_level' => 5],
            ['isbn' => '978-1501127625', 'title' => 'The Girl on the Train', 'author' => 'Paula Hawkins', 'genre' => 'Mystery', 'price' => 500, 'cost_price' => 280, 'stock_qty' => 4, 'reorder_level' => 10], // Low stock
            ['isbn' => '978-0062073488', 'title' => 'And Then There Were None', 'author' => 'Agatha Christie', 'genre' => 'Mystery', 'price' => 400, 'cost_price' => 200, 'stock_qty' => 30, 'reorder_level' => 5],
            ['isbn' => '978-0385504201', 'title' => 'The Da Vinci Code', 'author' => 'Dan Brown', 'genre' => 'Mystery', 'price' => 650, 'cost_price' => 350, 'stock_qty' => 45, 'reorder_level' => 15],
            ['isbn' => '978-1250301697', 'title' => 'The Silent Patient', 'author' => 'Alex Michaelides', 'genre' => 'Mystery', 'price' => 700, 'cost_price' => 400, 'stock_qty' => 22, 'reorder_level' => 8],

            // Biography & History
            ['isbn' => '978-1594205096', 'title' => 'Alexander Hamilton', 'author' => 'Ron Chernow', 'genre' => 'Biography', 'price' => 1100, 'cost_price' => 700, 'stock_qty' => 10, 'reorder_level' => 5],
            ['isbn' => '978-0385486804', 'title' => 'Into the Wild', 'author' => 'Jon Krakauer', 'genre' => 'Biography', 'price' => 550, 'cost_price' => 300, 'stock_qty' => 28, 'reorder_level' => 5],
            ['isbn' => '978-0062316097', 'title' => 'Sapiens', 'author' => 'Yuval Noah Harari', 'genre' => 'History', 'price' => 950, 'cost_price' => 600, 'stock_qty' => 85, 'reorder_level' => 20],
            ['isbn' => '978-0553384611', 'title' => 'A Brief History of Time', 'author' => 'Stephen Hawking', 'genre' => 'History', 'price' => 800, 'cost_price' => 450, 'stock_qty' => 2, 'reorder_level' => 5], // Low stock
        ];

        foreach ($books as $book) {
            Book::updateOrCreate(['isbn' => $book['isbn']], $book);
        }
    }
}
