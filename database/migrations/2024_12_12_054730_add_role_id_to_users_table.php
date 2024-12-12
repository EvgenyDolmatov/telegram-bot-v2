<?php

use App\Models\Role;
use App\Models\User;
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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->after('referrer_link')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();

            foreach (User::all() as $user) {
                if ($user->isAdmin()) {
                    $user->update(['role_id' => Role::where('code', 'admin')->first()->id]);
                    continue;
                }

                $user->update(['role_id' => Role::where('code', 'subscriber')->first()->id]);
            }

            $table->dropColumn('is_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->after('referrer_link')->default(false);

            foreach (User::all() as $user) {
                if ($user->role_id === Role::where('code', 'admin')->first()->id) {
                    $user->update(['is_admin' => true]);
                }
            }

            $table->dropColumn('role_id');
        });
    }
};
