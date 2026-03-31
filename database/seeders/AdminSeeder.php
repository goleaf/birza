<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    private const ADMIN_EMAIL = 'admin@admin.com';

    public function run(): void
    {
        $admin = Admin::query()->firstOrNew([
            'email' => self::ADMIN_EMAIL,
        ]);

        $admin->name = 'Admin';

        if (! $admin->exists) {
            $admin->password = Hash::make('password');
        }

        $admin->save();
    }
}
