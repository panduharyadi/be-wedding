<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::create([
            'name' => 'Owner Demo',
            'email' => 'owner@demo.com',
            'password' => bcrypt('password'),
        ]);

        $owner->assignRole('owner');
    }
}
