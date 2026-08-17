<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Shelf;
use Illuminate\Database\Seeder;

class DemoBooksSeeder extends Seeder
{
    /**
     * Demo catalog data for local development/staging only — realistic
     * enough to exercise search, filtering, and pagination against a
     * few hundred rows. Never run this against production; DatabaseSeeder
     * only calls it when the app is running locally.
     */
    public function run(): void
    {
        if (Author::query()->count() === 0) {
            Author::factory(25)->create();
        }

        $authors = Author::all();
        $categories = Category::all();
        $shelves = Shelf::all();

        if ($shelves->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Run CategorySeeder and LibrarySectionSeeder before DemoBooksSeeder.');

            return;
        }

        $position = 0;

        Book::factory()
            ->count(320)
            ->make()
            ->each(function (Book $book) use ($authors, $categories, $shelves, &$position) {
                $book->author()->associate($authors->random());
                $book->category()->associate($categories->random());

                // Shelve ~80% of the catalog; leave the rest as newly-catalogued/unshelved.
                if (fake()->boolean(80)) {
                    $position++;
                    $book->shelf()->associate($shelves->random());
                    $book->position = (string) $position;
                }

                $book->save();
            });
    }
}
