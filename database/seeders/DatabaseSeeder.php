<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        Role::create([
            'name_en' => 'admin',
            'name_ar' => 'admin',
        ]);
        Role::create([
            'name_en' => 'staff',
            'name_ar' => 'staff',
        ]);
        Role::create([
            'name_en' => 'customer',
            'name_ar' => 'customer',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Hazem',
            'email' => 'hazem.ismail@hotmail.com',
            'password' => '123123123',
            'role_id' => 1,
        ]);
    }
}
