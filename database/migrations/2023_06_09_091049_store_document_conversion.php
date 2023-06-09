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
        Schema::table('documents', function (Blueprint $table) {
            
            // Storage of the converted media to handle preview of
            // file that are not natively supported
            $table->string('conversion_disk_name')->nullable();

            $table->string('conversion_disk_path')->nullable();

            $table->string('conversion_file_mime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'conversion_disk_name',
                'conversion_disk_path',
                'conversion_file_mime',
            ]);
        });
    }
};
