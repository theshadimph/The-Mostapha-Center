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
        Schema::table('books', function (Blueprint $table) {
            // من اعتنى بهم — names of people who prepared/took care of the
            // text, distinct from editors/commentators/annotators. Same
            // JSON-array-of-names pattern as those three columns.
            $table->json('curators')->nullable()->after('annotators');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('curators');
        });
    }
};
