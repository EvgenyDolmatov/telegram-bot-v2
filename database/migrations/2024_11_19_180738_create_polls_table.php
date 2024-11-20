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
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tg_message_id');
            $table->text('question');
            $table->boolean('is_anonymous')->default(0);
            $table->boolean('allows_multiple_answers')->default(1);
            $table->enum('type', ['regular', 'quiz'])->default('regular');
            $table->integer('correct_option_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};
