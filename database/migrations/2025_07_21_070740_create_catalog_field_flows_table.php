<?php

use App\Models\Catalog;
use App\Models\CatalogField;
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
        Schema::create('catalog_field_flows', function (Blueprint $table) {
            $table->id();
            
            $table->uuid()->unique();
            
            $table->foreignIdFor(Catalog::class);

            $table->foreignIdFor(User::class); // Creator of the action
            
            $table->string('title');

            $table->mediumText('description')->nullable();

            $table->integer('action'); // The action to execute (e.g., 'summarize', 'translate', etc.)

            $table->foreignIdFor(CatalogField::class, 'source_field_id'); // The field to read from
            
            $table->foreignIdFor(CatalogField::class, 'target_field_id'); // The field to write to
            
            $table->json('configuration')->nullable();

            $table->boolean('auto_trigger')->default(false); // Whether to trigger automatically on entry creation

            $table->boolean('overwrite_existing')->default(false); // Whether to overwrite existing values            
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_field_flows');
    }
};
