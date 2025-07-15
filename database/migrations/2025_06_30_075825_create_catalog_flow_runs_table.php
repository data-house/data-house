<?php

use App\Models\CatalogFlow;
use App\Models\Document;
use App\Models\ImportStatus;
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
        Schema::create('catalog_flow_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(CatalogFlow::class);
            $table->foreignIdFor(Document::class);
            $table->unsignedSmallInteger('status')->default(ImportStatus::CREATED->value);
            $table->json('run_result')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_flow_runs');
    }
};
