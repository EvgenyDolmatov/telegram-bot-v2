<?php

use App\Models\State;
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
        foreach (State::all() as $state) {
            switch ($state->code) {
                case 'type_choice':
                    $state->update(['code' => 'poll_type_choice']);
                    break;
                case 'anon_choice':
                    $state->update(['code' => 'poll_anonymity_choice']);
                    break;
                case 'difficulty_choice':
                    $state->update(['code' => 'poll_difficulty_choice']);
                    break;
                case 'sector_choice':
                    $state->update(['code' => 'poll_sector_choice']);
                    break;
                case 'subject_choice':
                    $state->update(['code' => 'poll_subject_choice']);
                    break;
                case 'theme_request':
                    $state->update(['code' => 'poll_theme_waiting']);
                    break;
                case 'ai_response':
                    $state->update(['code' => 'poll_ai_responded_choice']);
                    break;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('states', function (Blueprint $table) {
            //
        });
    }
};
