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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->ulid()->unique();

            $table->timestamps();

            // Storage location of the file
            $table->string('disk_name');
            $table->string('disk_path');

            $table->string('title');
            
            $table->string('mime');

            $table->boolean('draft')->default(true);

            $table->foreignIdFor(User::class, 'uploaded_by');

            $table->foreignIdFor(Team::class)->nullable();

            $table->json('languages')->default('[]');

            $table->text('description')->nullable();

            // Storage location of the thumbnail, if any
            $table->string('thumbnail_disk_name')->nullable();

            $table->string('thumbnail_disk_path')->nullable();
            
            // Publication details
            $table->dateTime('published_at')->nullable();
            
            $table->foreignIdFor(User::class, 'published_by')->nullable();
            
            $table->string('published_to_url')->nullable();            
            
            // For future usage
            $table->json('properties')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
