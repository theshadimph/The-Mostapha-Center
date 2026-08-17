<?php

namespace Database\Factories;

use App\Models\LibrarySection;
use App\Models\Shelf;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shelf>
 */
class ShelfFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $n = 0;
        $n++;

        return [
            'library_section_id' => LibrarySection::factory(),
            'code' => str_pad((string) $n, 2, '0', STR_PAD_LEFT),
            'description' => null,
        ];
    }
}
