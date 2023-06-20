<?php

use App\Models\ImportStatus;
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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            
            $table->ulid()->unique();
            
            $table->foreignIdFor(User::class, 'created_by');
            
            $table->unsignedSmallInteger('status')->default(ImportStatus::CREATED->value);
            
            $table->timestamps();
            
            $table->string('source');
            
            $table->longText('configuration');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
