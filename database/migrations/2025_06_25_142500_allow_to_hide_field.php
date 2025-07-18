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
        Schema::table('catalog_fields', function (Blueprint $table) {
            $table->boolean('make_hidden')->after('description')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_fields', function (Blueprint $table) {
            $table->dropColumn('make_hidden');
        });
    }
};
