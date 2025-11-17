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
        Schema::create('chat_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('thread_id', 255)->nullable()->charset('utf8mb4')->collation('utf8mb4_0900_ai_ci');
            $table->longText('messages')->nullable()->charset('utf8mb4')->collation('utf8mb4_0900_ai_ci');
            $table->timestamps();

            // Indexes
            $table->unique('thread_id', 'uk_thread_id');
            $table->index('thread_id', 'idx_thread_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_history');
    }
};
