<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Мужской', 'Женский'] as $name) {
            Gender::firstOrCreate(['name' => $name]);
        }
    }
}
