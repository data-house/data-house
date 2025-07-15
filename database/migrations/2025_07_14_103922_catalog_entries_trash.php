<?php

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
        Schema::table('catalog_entries', function (Blueprint $table) {
            $table->softDeletes('trashed_at')->index();
            $table->foreignIdFor(User::class, 'trashed_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_entries', function (Blueprint $table) {
            //
        });
    }
};
