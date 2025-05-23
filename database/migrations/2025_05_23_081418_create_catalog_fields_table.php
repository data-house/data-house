<?php

use App\Models\Catalog;
use App\Models\SkosCollection;
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
        Schema::create('catalog_fields', function (Blueprint $table) {
            $table->id();
            
            $table->uuid()->unique();
            
            $table->foreignIdFor(Catalog::class);

            $table->foreignIdFor(User::class);
            
            $table->unsignedSmallInteger('data_type'); // backed by CatalogFieldType enum

            $table->unsignedInteger('order'); // used for sorting

            $table->string('title');

            $table->mediumText('description')->nullable();            

            $table->foreignIdFor(User::class, 'updated_by')->nullable();
            
            $table->foreignIdFor(SkosCollection::class)->nullable(); // in case column is a managed vocabulary column with possible values in a collection

            $table->json('constraints')->nullable(); // to track validation checks to apply before data is entered
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_fields');
    }
};
