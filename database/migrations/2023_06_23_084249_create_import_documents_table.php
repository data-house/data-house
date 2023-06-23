<?php

use App\Models\Import;
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
        Schema::create('import_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Import::class);
            
            $table->string('source_path');
            
            $table->string('mime');

            $table->string('disk_name');
            
            $table->string('disk_path')->nullable();
            
            $table->string('retrieved_at')->nullable();
            
            $table->string('processed_at')->nullable();

            $table->foreignIdFor(User::class, 'uploaded_by');
            
            $table->foreignIdFor(Team::class)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_documents');
    }
};
