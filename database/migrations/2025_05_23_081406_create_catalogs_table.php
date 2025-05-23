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
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            
            $table->uuid()->unique();
            
            $table->foreignIdFor(User::class);
            
            $table->foreignIdFor(Team::class);
            
            $table->string('title');
            
            $table->mediumText('description')->nullable();
            
            $table->foreignIdFor(User::class, 'updated_by')->nullable();
            
            $table->unsignedSmallInteger('visibility');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
