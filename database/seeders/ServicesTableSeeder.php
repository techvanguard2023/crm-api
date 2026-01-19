<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Hospedagem',
                'description' => 'Hospedagem de sites e aplicativos.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Desenvolvimento',
                'description' => 'Desenvolvimento de software sob medida.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Suporte',
                'description' => 'Suporte técnico e manutenção.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('services')->insert($services);
    }
}
