<?php

use App\Models\Question;
use App\Models\QuestionReview;
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
        Schema::create('question_reviews', function (Blueprint $table) {
            $table->id();

            $table->timestamps();

            $table->foreignIdFor(Team::class);

            $table->foreignIdFor(User::class);
            
            $table->foreignIdFor(Question::class);

            $table->unsignedInteger('status');
            
            $table->unsignedInteger('evaluation_result')->nullable();

            $table->text('remarks')->nullable();
        });

        Schema::create('question_review_user', function (Blueprint $table) {
            $table->id();
            
            $table->timestamps();

            $table->foreignIdFor(User::class);
            
            $table->foreignIdFor(QuestionReview::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_review_user');

        Schema::dropIfExists('question_reviews');
    }
};
