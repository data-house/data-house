<?php

use App\Models\Document;
use App\Models\SkosConcept;
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
        Schema::create('document_skos_concept', function (Blueprint $table) {
            $table->foreignIdFor(Document::class);
            $table->foreignIdFor(SkosConcept::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_skos_concept');
    }
};
