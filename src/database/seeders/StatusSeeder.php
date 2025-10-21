<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

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
        ]);
    }
}
