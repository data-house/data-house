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
        Schema::table('projects', function (Blueprint $table) {
            
            $table->ulid()->unique()->after('id')->nullable();

            $table->renameColumn('name', 'title');

            $table->renameColumn('sectors', 'topics');
            
            $table->unsignedSmallInteger('type')->nullable();

            $table->json('organizations')->nullable();

            $table->json('properties')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('title', 'name');

            $table->renameColumn('topics', 'sectors' );

            $table->dropColumn('ulid');

            $table->dropColumn('organizations');
            
            $table->dropColumn('properties');
            
            $table->dropColumn('type');
        });
    }
};
