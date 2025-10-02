<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::insert([
            ['id' => 1, 'status' => 1],
            ['id' => 2, 'status' => 2],
            ['id' => 3, 'status' => 3],
            ['id' => 4, 'status' => 4],
        ]);
    }
}
