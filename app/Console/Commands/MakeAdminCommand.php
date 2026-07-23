<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\School;
use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeAdminCommand extends Command
{
    protected $signature = 'make:admin';

    protected $description = 'Create an admin user interactively';

    public function handle(): int
    {
        $username = $this->ask('Enter username');
        $name = $this->ask('Enter name');
        $password = $this->secret('Enter password');
        $email = $this->ask('Enter email (optional, press enter to skip)');

        $roleChoice = $this->choice(
            'Select role',
            ['1 — Super Admin', '2 — District Admin', '3 — School Admin'],
            0
        );

        $roleMap = [
            '1 — Super Admin'   => 'super_admin',
            '2 — District Admin'=> 'district_admin',
            '3 — School Admin'  => 'school_admin',
        ];
        $role = $roleMap[$roleChoice];

        $districtId = null;
        $schoolId = null;

        if ($role === 'district_admin') {
            $districts = District::orderBy('name')->get(['id', 'name']);
            if ($districts->isEmpty()) {
                $this->error('No districts found. Run district seeder first.');

                return self::FAILURE;
            }
            $this->table(['ID', 'Name'], $districts->map(fn ($d) => [$d->id, $d->name]));
            $districtId = (int) $this->ask('Select district ID');
        }

        if ($role === 'school_admin') {
            $schools = School::orderBy('name')->get(['id', 'name', 'code']);
            if ($schools->isEmpty()) {
                $this->error('No schools found. Create schools first.');

                return self::FAILURE;
            }
            $this->table(['ID', 'Code', 'Name'], $schools->map(fn ($s) => [$s->id, $s->code, $s->name]));
            $schoolId = (int) $this->ask('Select school ID');
        }

        $validator = Validator::make([
            'username' => $username,
            'name'     => $name,
            'password' => $password,
            'email'    => $email ?: null,
        ], [
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'name'     => ['required', 'string', 'max:150'],
            'password' => ['required', new StrongPassword],
            'email'    => ['nullable', 'email', 'max:150', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'username'    => $username,
            'name'        => $name,
            'email'       => $email ?: null,
            'password'    => Hash::make($password),
            'role'        => $role,
            'district_id' => $districtId,
            'school_id'   => $schoolId,
            'is_active'   => true,
        ]);

        $user->assignRole($role);

        activity('user')
            ->causedBy($user)
            ->performedOn($user)
            ->log('User created via make:admin command');

        $this->info("User created successfully. Username: {$username}");

        return self::SUCCESS;
    }
}
