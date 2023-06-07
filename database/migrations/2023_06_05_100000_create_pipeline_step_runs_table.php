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
        Schema::create('pipeline_step_runs', function (Blueprint $table) {
            $table->id();

            $table->timestamps();
            
            $table->ulid()->unique();

            $table->foreignIdFor(Pipeline::pipelineRunModel(), 'pipeline_run_id');

            $table->string('trigger')->nullable();

            $table->string('status');

            $table->string('job');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipeline_step_runs');
    }
};
