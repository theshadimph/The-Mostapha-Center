<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('status', 32)->default('uploaded')->index();

            // Column-mapping step: {excel_column_index: book_field}. Category
            // mapping step: {raw_excel_category_value: resolved_category_id}.
            // Both built interactively before the heavy row-processing job runs,
            // so processing never has to guess or create categories on the fly.
            $table->json('column_mapping')->nullable();
            $table->json('category_mapping')->nullable();

            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->unsignedInteger('validation_failed_count')->default(0);
            $table->unsignedInteger('unknown_category_count')->default(0);
            $table->unsignedInteger('unknown_author_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
