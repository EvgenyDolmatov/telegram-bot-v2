<?php

namespace Database\Seeders;

use App\Models\Sector;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sectors = Sector::all();
        $subjectData = [];

        foreach ($sectors as $sector) {
            if ($sector->code === 'school') {
                $subjectData = [
                    [
                        'code' => 'math',
                        'title' => 'Математика',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'russian_language',
                        'title' => 'Русский язык',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'physics',
                        'title' => 'Физика',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'geography',
                        'title' => 'География',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'chemistry',
                        'title' => 'Химия',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'biology',
                        'title' => 'Биология',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'history',
                        'title' => 'История',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'social_science',
                        'title' => 'Обществознание',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'foreign_languages',
                        'title' => 'Иностранные языки',
                        'has_child' => true,
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'english_language',
                        'title' => 'Английский язык',
                        'parent_id' => 8,
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'german_language',
                        'title' => 'Немецкий язык',
                        'parent_id' => 8,
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'french_language',
                        'title' => 'Французский язык',
                        'parent_id' => 8,
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'chinese_language',
                        'title' => 'Китайский язык',
                        'parent_id' => 8,
                        'sector_id' => $sector->id,
                    ],
                ];
            }

            if ($sector->code === 'economic') {
                $subjectData = [
                    [
                        'code' => 'marketing',
                        'title' => 'Маркетинг',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'economy',
                        'title' => 'Экономика',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'management',
                        'title' => 'Менеджмент',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'finance_and_accounting',
                        'title' => 'Финансы и бухгалтерский учет',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'logistics',
                        'title' => 'Логистика',
                        'sector_id' => $sector->id,
                    ],
                ];
            }

            if ($sector->code === 'it') {
                $subjectData = [
                    [
                        'code' => 'programming',
                        'title' => 'Программирование',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'cyber_security',
                        'title' => 'Кибербезопасность',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'web_development',
                        'title' => 'Веб-разработка',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'databases',
                        'title' => 'Базы данных',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'artificial_intelligence',
                        'title' => 'Искусственный интеллект',
                        'sector_id' => $sector->id,
                    ],
                ];
            }

            if ($sector->code === 'design') {
                $subjectData = [
                    [
                        'code' => 'graphic_design',
                        'title' => 'Графический дизайн',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'ui_ux',
                        'title' => 'UI/UX дизайн',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'architecture_and_interior',
                        'title' => 'Архитектура и интерьер',
                        'sector_id' => $sector->id,
                    ],
                    [
                        'code' => 'art_and_drawing',
                        'title' => 'Искусство и рисование',
                        'sector_id' => $sector->id,
                    ],
                ];
            }

            if ($sector->code === 'fun') {
                $subjectData = [
                    [
                        'code' => 'fun',
                        'title' => 'Развлечения',
                        'sector_id' => $sector->id,
                    ],
                ];
            }


            if (count($subjectData) !== 0) {
                foreach ($subjectData as $subjectItem) {
                    Subject::create($subjectItem);
                }
            }

        }
    }
}
