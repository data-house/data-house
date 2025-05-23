<?php

use App\Models\DocumentSection;
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
        Schema::table('document_summaries', function (Blueprint $table) {
            $table->foreignIdFor(DocumentSection::class)->nullable();
            $table->boolean('all_document')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_summaries', function (Blueprint $table) {
            $table->dropForeignIdFor(DocumentSection::class);
            $table->dropColumn("all_document");
        });
    }
};
