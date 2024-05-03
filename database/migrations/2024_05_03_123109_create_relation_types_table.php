<?php

use App\Models\LinkedDocument;
use App\Models\RelationType;
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
        Schema::create('relation_types', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
        });

        Schema::create('linked_document_relation_type', function (Blueprint $table) {
            $table->id();
            
            $table->timestamps();

            $table->foreignId('linked_document_id')->references('id')->on('collection_document');
            
            $table->foreignIdFor(RelationType::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concept_relations');
    }
};
