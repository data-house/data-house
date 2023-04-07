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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->timestamps();

            $table->string('name');

            $table->string('slug')->index();

            // A project can pertain to multiple sectors,
            // so far the list of sectors is fixed using an enumeration
            $table->json('sectors')->default('[]');
            
            // A project can bi-lateral or regional so it involves multiple countries,
            // so far the list of country code is fixed using an enumeration
            $table->json('countries')->default('[]');
            
            $table->text('description')->nullable();

            $table->dateTime('starts_at')->nullable();
            
            $table->dateTime('ends_at')->nullable();

            // Project website, if any
            $table->string('website')->nullable();

            // URL of connected resources or sources of interest
            $table->json('links')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
