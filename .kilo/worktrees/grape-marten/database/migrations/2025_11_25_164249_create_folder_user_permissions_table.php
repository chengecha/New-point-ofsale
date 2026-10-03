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
        Schema::create('folder_user_permissions', function (Blueprint $table) {
          $table->id();

            // Relations
            $table->foreignId('folder_id')
                ->constrained('directories')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Permission flags
            $table->boolean('can_read')->default(true);
            $table->boolean('can_upload')->default(false);
            $table->boolean('can_delete')->default(false);

            $table->timestamps();

            // Prevent duplicates
            $table->unique(['folder_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folder_user_permissions');
    }
};
