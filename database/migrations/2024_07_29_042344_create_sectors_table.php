<?php

use App\Models\Sector;
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
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
        });

        $sectorData = [
            ['code' => 'school', 'title' => 'Школа/ЕГЭ/ОГЭ'],
            ['code' => 'economic', 'title' => 'Экономика'],
            ['code' => 'it', 'title' => 'Информационные технологии'],
            ['code' => 'design', 'title' => 'Творчество и дизайн'],
            ['code' => 'fun', 'title' => 'Развлечения'],
        ];

        foreach ($sectorData as $sectorItem) {
            Sector::create($sectorItem);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sectors');
    }
};
