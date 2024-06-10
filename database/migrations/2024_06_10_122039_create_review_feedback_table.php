<?php

use App\Models\QuestionReview;
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
        Schema::create('review_feedback', function (Blueprint $table) {
            $table->id();
            
            $table->timestamps();

            $table->foreignIdFor(QuestionReview::class);

            $table->foreignIdFor(User::class, 'reviewer_user_id');

            $table->unsignedInteger('vote')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_feedback');
    }
};
