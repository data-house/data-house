<?php

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
        Schema::create('skos_relation', function (Blueprint $table) {
            // $table->id();
            $table->foreignIdFor(SkosConcept::class, 'source_skos_concept_id');
            $table->foreignIdFor(SkosConcept::class, 'target_skos_concept_id');
            $table->unsignedInteger('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skos_relation');
    }
};
