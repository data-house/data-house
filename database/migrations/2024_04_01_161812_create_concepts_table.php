<?php

use App\Models\Concept;
use App\Models\ConceptCollection;
use App\Models\ConceptRelationType;
use App\Models\ConceptScheme;
use App\Models\User;
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
        Schema::create('concepts', function (Blueprint $table) {
            $table->id();

            $table->timestamps();

            $table->foreignIdFor(User::class); // dc:creator

            $table->string('title'); // skos:prefLabel || dc:title
            
            $table->text('description')->nullable(); // skos:note || skos:definition || dc:description
        });

        Schema::create('concept_schemes', function (Blueprint $table) {
            $table->id();

            $table->timestamps();

            $table->foreignIdFor(User::class);

            $table->string('title'); // dc:title
            
            $table->text('description')->nullable(); // skos:note || skos:definition || dc:description
        });
        
        Schema::create('concept_collections', function (Blueprint $table) {
            $table->id();

            $table->timestamps();

            $table->foreignIdFor(User::class);

            $table->string('title'); // dc:title
            
            $table->text('description')->nullable();
        });

        Schema::create('concept_in_schemes', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Concept::class);
            
            $table->foreignIdFor(ConceptScheme::class);
            
            $table->boolean('is_top_concept')->default(false);
            
            $table->timestamps();
        });
        
        Schema::create('concept_collection_members', function (Blueprint $table) {
            $table->id();
            
            $table->foreignIdFor(ConceptCollection::class);

            $table->foreignIdFor(Concept::class);
            
            $table->timestamps();
        });

        Schema::create('concept_relationships', function (Blueprint $table) {
            $table->id();
            
            $table->foreignIdFor(Concept::class, 'source');
            
            $table->foreignIdFor(Concept::class, 'target');
            
            $table->unsignedSmallInteger('type')->default(ConceptRelationType::RELATED->value);
            
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concept_collection_members');

        Schema::dropIfExists('concept_relationships');
        
        Schema::dropIfExists('concept_in_schemes');
        
        Schema::dropIfExists('concept_collections');

        Schema::dropIfExists('concept_schemes');
        
        Schema::dropIfExists('concepts');
    }
};
