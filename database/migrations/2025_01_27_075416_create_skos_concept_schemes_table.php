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
        Schema::create('skos_concept_schemes', function (Blueprint $table) {
            $table->id();
            $table->text('uri')->unique();
            $table->text('vocabulary_base_uri')->nullable();
            $table->string('prefix')->nullable();
            $table->text('pref_label')->nullable();
            $table->json('alt_labels')->nullable();
            $table->json('hidden_labels')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skos_concept_schemes');
    }
};
