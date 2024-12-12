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
            $table->unsignedBigInteger('role_id')->after('referrer_link')->nullable();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();

            foreach (User::all() as $user) {
                if ($user->is_admin) {
                    $user->update(['role_id' => Role::where('code', 'admin')->first()->id]);
                    continue;
                }

                $user->update(['role_id' => Role::where('code', 'subscriber')->first()->id]);
            }

            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropCo('is_admin');
            }
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
                if ($user->isAdmin()) {
                    $user->update(['is_admin' => true]);
                }
            }

            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
        });
    }
};
