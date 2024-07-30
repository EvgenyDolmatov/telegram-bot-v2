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
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tg_chat_id');
            $table->json('user_flow')->nullable();
            $table->json('ai_survey')->nullable();
            $table->json('usage_prompt_tokens')->nullable();
            $table->bigInteger('usage_prompt_tokens')->nullable();
            $table->bigInteger('usage_completion_tokens')->nullable();
            $table->bigInteger('usage_total_tokens')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};
