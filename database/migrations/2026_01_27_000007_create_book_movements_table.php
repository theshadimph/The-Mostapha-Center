<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit trail of every physical relocation of a book, so staff can answer
     * both "where is this book now" (books.shelf_id/position) and
     * "where was it before" (this table).
     */
    public function up(): void
    {
        Schema::create('book_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();

            $table->foreignId('from_shelf_id')->nullable()->constrained('shelves')->nullOnDelete();
            $table->string('from_position', 16)->nullable();

            $table->foreignId('to_shelf_id')->nullable()->constrained('shelves')->nullOnDelete();
            $table->string('to_position', 16)->nullable();

            $table->foreignId('moved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['book_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_movements');
    }
};
