<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stored_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('path');
            $table->bigInteger('size');
            $table->string('mime_type')->nullable();
            $table->string('folder')->nullable();
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('filename');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_files');
    }
};