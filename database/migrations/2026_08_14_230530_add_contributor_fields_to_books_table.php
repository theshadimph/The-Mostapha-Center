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
            // Each book may have several editors/commentators/annotators (e.g.
            // a manuscript verified by more than one محقق), so these are
            // JSON arrays of names rather than single columns or a relation
            // to Author — there's no need for these names to have their own
            // slugs/bios/admin pages the way a primary Author does.
            $table->json('editors')->nullable()->after('author_id');
            $table->json('commentators')->nullable()->after('editors');
            $table->json('annotators')->nullable()->after('commentators');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['editors', 'commentators', 'annotators']);
        });
    }
};
