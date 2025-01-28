<?php

use App\Models\SkosCollection;
use App\Models\SkosConcept;
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
        Schema::create('skos_collections', function (Blueprint $table) {
            $table->id();
            $table->text('uri')->unique();
            $table->foreignIdFor(SkosConceptScheme::class)->nullable();
            $table->text('pref_label');
            $table->json('alt_labels')->nullable();
            $table->json('hidden_labels')->nullable();


            $table->string('notation')->nullable(); // or dc:identifier
            $table->text('definition')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });

        Schema::create('skos_collection_skos_concept', function (Blueprint $table) {
            $table->foreignIdFor(SkosCollection::class);
            $table->foreignIdFor(SkosConcept::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skos_collection_skos_concept');

        Schema::dropIfExists('skos_collections');
    }
};
