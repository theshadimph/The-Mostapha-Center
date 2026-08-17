<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            // Stable identifier — survives location changes, used for QR/barcode labels.
            $table->string('barcode', 32)->unique();

            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('author_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->string('edition')->nullable();
            $table->string('isbn', 32)->nullable()->unique();
            $table->unsignedInteger('pages')->nullable();
            $table->string('language', 8)->default('ar');
            $table->string('cover_image')->nullable();
            $table->string('status', 32)->default('available')->index();

            // Current physical location — nullable (a newly catalogued book may not be shelved yet).
            $table->foreignId('shelf_id')->nullable()->constrained()->nullOnDelete();
            $table->string('position', 16)->nullable();

            $table->unsignedInteger('views_count')->default(0);

            $table->timestamps();

            $table->index('title');
            $table->index(['shelf_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
