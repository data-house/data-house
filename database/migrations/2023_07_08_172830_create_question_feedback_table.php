<?php

use App\Models\Question;
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
        Schema::create('question_feedback', function (Blueprint $table) {
            $table->id();

            $table->timestamps();

            $table->foreignIdFor(Question::class);

            $table->foreignIdFor(User::class);

            $table->unsignedSmallInteger('vote');
            
            $table->smallInteger('points');

            $table->unsignedSmallInteger('reason')->nullable();
            
            $table->text('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_feedback');
    }
};
