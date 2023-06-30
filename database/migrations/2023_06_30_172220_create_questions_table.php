<?php

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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->uuid();

            $table->timestamps();

            $table->text('question');
            
            $table->string('hash', 128)->index();

            $table->morphs('questionable');

            $table->foreignIdFor(User::class)->nullable();

            $table->string('language')->nullable();
            
            $table->json('answer')->nullable();

            $table->float('execution_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
