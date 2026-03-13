<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,         // admin first
            JobSeeder::class,
            SeekerProfileSeeder::class,
            CompanyProfileSeeder::class,
            ApplicationSeeder::class,
            SavedJobSeeder::class,
        ]);
    }
}
