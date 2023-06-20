<?php

use App\Models\Import;
use App\Models\ImportStatus;
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
        Schema::create('import_maps', function (Blueprint $table) {
            $table->id();
            
            $table->ulid()->unique();
            
            $table->timestamps();

            $table->foreignIdFor(Import::class);

            $table->unsignedSmallInteger('status')->default(ImportStatus::CREATED->value);

            $table->foreignIdFor(Team::class, 'mapped_team')->nullable();
            
            $table->foreignIdFor(User::class, 'mapped_uploader')->nullable();

            $table->boolean('recursive')->default(false);
            
            $table->mediumText('filters')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_maps');
    }
};
