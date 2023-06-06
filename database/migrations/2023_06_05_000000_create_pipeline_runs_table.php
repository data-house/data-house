<?php

use App\Pipelines\Pipeline;
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
        Schema::create('pipeline_runs', function (Blueprint $table) {
            $table->id();

            $table->ulid()->unique();

            $table->timestamps();

            $table->morphs('pipeable');
            
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipeline_runs');
    }
};
