<?php

use App\Models\Team;
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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            
            $table->ulid();

            $table->timestamps();

            $table->foreignIdFor(User::class)->nullable();

            $table->foreignIdFor(Team::class)->nullable();

            $table->string('title');

            $table->unsignedSmallInteger('type');

            $table->unsignedSmallInteger('visibility');
            
            $table->unsignedTinyInteger('draft');

            $table->unsignedSmallInteger('strategy');

            $table->json('strategy_configuration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
