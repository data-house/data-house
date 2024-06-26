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
        Schema::table('import_maps', function (Blueprint $table) {
            $table->timestamp('last_session_started_at')->nullable();
            $table->timestamp('last_session_completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_maps', function (Blueprint $table) {
            //
        });
    }
};
