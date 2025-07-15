<?php

use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogField;
use App\Models\Document;
use App\Models\SkosConcept;
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
        Schema::create('catalog_values', function (Blueprint $table) {
            $table->id();

            $table->uuid()->unique();

            $table->foreignIdFor(Catalog::class);

            $table->foreignIdFor(CatalogEntry::class);

            $table->foreignIdFor(CatalogField::class);
            
            $table->foreignIdFor(User::class); // in case is the automation that creates the data, we might want to create a system-wide librarian ghost user

            $table->foreignIdFor(User::class, 'updated_by')->nullable();

            $table->mediumText('value_text')->nullable(); // TEXT
            
            $table->integer('value_int')->nullable(); //    BIGINT
            
            $table->timestamp('value_date')->nullable(); //   DATE
            
            $table->float('value_float')->nullable(); //  NUMERIC

            $table->boolean('value_bool')->nullable(); //  Tinyint
            
            // $table->string('value_morph_id')->nullable(); //  
            // $table->string('value_morph_type')->nullable(); //  

            $table->foreignIdFor(SkosConcept::class, 'value_concept')->nullable(); // REF<Concept>  


            
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_values');
    }
    
    // public function shouldRun(): bool
    // {
    //     return false;
    // }
};
