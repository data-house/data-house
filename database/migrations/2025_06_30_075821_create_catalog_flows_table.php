<?php

use App\Models\Catalog;
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
        Schema::create('catalog_flows', function (Blueprint $table) {
            $table->id();
            
            $table->uuid()->unique();
            
            $table->unsignedSmallInteger('trigger');
            
            $table->unsignedSmallInteger('target_entity');

            $table->foreignIdFor(User::class);

            $table->foreignIdFor(Catalog::class)->index();

            $table->string('title');

            $table->mediumText('description')->nullable();

            $table->json('configuration')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_flows');
    }
};
