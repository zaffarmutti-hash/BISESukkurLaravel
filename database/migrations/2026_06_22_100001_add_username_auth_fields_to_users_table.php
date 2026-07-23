<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 100)->nullable()->unique()->after('id');
            $table->foreignId('district_id')->nullable()->after('school_id')->constrained('districts')->nullOnDelete();
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('last_login_at');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->boolean('must_change_password')->default(false)->after('locked_until');
        });

        $users = DB::table('users')->whereNull('username')->get();
        foreach ($users as $user) {
            $base = $user->email
                ? strstr($user->email, '@', true)
                : 'user_'.$user->id;
            $username = $base;
            $suffix = 1;
            while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $base.'_'.$suffix++;
            }
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN username SET NOT NULL');
            DB::statement('ALTER TABLE users ALTER COLUMN email DROP NOT NULL');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 100)->nullable(false)->change();
                $table->string('email', 150)->nullable()->change();
            });
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['super_admin'::text, 'district_admin'::text, 'school_admin'::text]))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'district_admin', 'school_admin') NOT NULL DEFAULT 'school_admin'");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->index('username');
            $table->index('district_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
            $table->dropIndex(['username']);
            $table->dropIndex(['district_id']);
            $table->dropColumn([
                'username', 'district_id', 'last_login_at',
                'failed_login_attempts', 'locked_until', 'must_change_password',
            ]);
        });
    }
};
