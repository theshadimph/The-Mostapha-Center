<?php

namespace Database\Seeders;

use App\Models\LibrarySection;
use Illuminate\Database\Seeder;

class LibrarySectionSeeder extends Seeder
{
    /**
     * A starter physical layout — the librarian can rename/add sections and
     * shelves later from the admin area. Safe to run in production as a
     * bootstrap template.
     */
    public function run(): void
    {
        $sections = [
            ['name' => 'قاعة العلوم الشرعية', 'code' => 'F01', 'floor' => 'الأرضي', 'shelves' => 10],
            ['name' => 'قاعة التاريخ والأدب', 'code' => 'F02', 'floor' => 'الأول', 'shelves' => 8],
            ['name' => 'قاعة المراجع العامة', 'code' => 'F03', 'floor' => 'الأول', 'shelves' => 6],
        ];

        foreach ($sections as $data) {
            $section = LibrarySection::query()->firstOrCreate(
                ['code' => $data['code']],
                ['name' => $data['name'], 'floor' => $data['floor']],
            );

            for ($i = 1; $i <= $data['shelves']; $i++) {
                $section->shelves()->firstOrCreate([
                    'code' => str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }
}
