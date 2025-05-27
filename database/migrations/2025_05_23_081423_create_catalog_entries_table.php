<?php

use App\Models\Catalog;
use App\Models\CatalogField;
use App\Models\Document;
use App\Models\Project;
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
        Schema::create('catalog_entries', function (Blueprint $table) {
            $table->id();
            
            $table->uuid()->unique();

            $table->foreignIdFor(Catalog::class);

            $table->integer('entry_index')->nullable(); // a user oriented reference to the row within the catalog

            $table->foreignIdFor(User::class); // in case is the automation that creates the data, we might want to create a system-wide librarian ghost user
            
            $table->foreignIdFor(Document::class)->nullable();
            
            $table->foreignIdFor(Project::class)->nullable();

            $table->foreignIdFor(User::class, 'updated_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_entries');
    }

    // public function shouldRun(): bool
    // {
    //     return false;
    // }
};
