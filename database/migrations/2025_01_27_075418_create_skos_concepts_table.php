<?php

use App\Models\SkosConceptScheme;
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
        Schema::create('skos_concepts', function (Blueprint $table) {

            // Assumes only English, otherwise separate tables for labels and text localization must be implemented

            $table->id();
            $table->text('uri')->unique();
            $table->text('pref_label');

            $table->foreignIdFor(SkosConceptScheme::class)->nullable();

            // $table->uuid('broader_concept_id')->nullable();


            $table->json('alt_labels')->nullable();
            $table->json('hidden_labels')->nullable();


            $table->string('notation')->nullable(); // or dc:identifier
            $table->text('definition')->nullable();
            $table->text('note')->nullable();
            $table->boolean('top_concept')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skos_concepts');
    }
};
