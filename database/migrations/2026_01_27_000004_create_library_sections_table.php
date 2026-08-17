<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A library section is a physical hall/room/wing of the building
     * (e.g. "قاعة العلوم الشرعية"). Shelves belong to a section.
     */
    public function up(): void
    {
        Schema::create('library_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 16)->unique();
            $table->string('floor')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_sections');
    }
};
