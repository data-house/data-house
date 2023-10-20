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
        Schema::table('import_documents', function (Blueprint $table) {
            
            // Date of the document as reported from the source file system, usually the last modified date
            $table->dateTime('document_date')->nullable();
            
            // Size in bytes as reported by the source filesystem
            $table->unsignedInteger('document_size')->nullable();
            
            // Checksum of the file content calculated after file transfer
            $table->string('document_hash', 128)->nullable();
            
            // Hash of source_path, used to identify possible candidates of duplication
            $table->string('import_hash', 64)->nullable()->index();

            $table->unsignedSmallInteger('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_documents', function (Blueprint $table) {
            // No rollback to prevent data loss
        });
    }
};
