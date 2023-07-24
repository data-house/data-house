<?php

use App\Models\Question;
use App\Models\QuestionRelation;
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
        Schema::create('question_relationship', function (Blueprint $table) {
            $table->id();
            
            $table->foreignIdFor(Question::class, 'source');
            
            $table->foreignIdFor(Question::class, 'target');
            
            $table->unsignedSmallInteger('type')->default(QuestionRelation::CONNECT->value);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_relationship');
    }
};
