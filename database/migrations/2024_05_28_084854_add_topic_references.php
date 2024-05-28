<?php

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
        Schema::table('collections', function (Blueprint $table) {
            // This is a temporary solution to allow the definition of a collection hierarchy
            // The topic_group can be considered the parent
            // TODO: Remove this solution once concepts are modelled following SKOS and collections have a native hierarchy
            $table->string('topic_name')->nullable()->index();
            $table->string('topic_group')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            //
        });
    }
};
