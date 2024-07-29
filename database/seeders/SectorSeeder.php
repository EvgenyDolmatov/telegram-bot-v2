<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
}
