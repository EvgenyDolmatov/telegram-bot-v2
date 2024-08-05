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
        Schema::create('state_buttons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->references('id')->on('states')->cascadeOnDelete();
            $table->string('text');
            $table->string('callback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_buttons');
    }
};
