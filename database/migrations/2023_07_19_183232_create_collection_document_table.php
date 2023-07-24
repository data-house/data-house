<?php

use App\Models\Collection;
use App\Models\Document;
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
        Schema::create('collection_document', function (Blueprint $table) {
            $table->id();
            
            $table->timestamps();

            $table->foreignIdFor(Collection::class);
            
            $table->foreignIdFor(Document::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_document');
    }
};
