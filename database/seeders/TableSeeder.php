<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = [
            ['table_no' => 'T001', 'capacity' => 2, 'is_active' => true],
            ['table_no' => 'T002', 'capacity' => 4, 'is_active' => true],
            ['table_no' => 'T003', 'capacity' => 4, 'is_active' => true],
            ['table_no' => 'T004', 'capacity' => 6, 'is_active' => true],
            ['table_no' => 'T005', 'capacity' => 2, 'is_active' => true],
            ['table_no' => 'T006', 'capacity' => 8, 'is_active' => true],
            ['table_no' => 'T007', 'capacity' => 4, 'is_active' => true],
            ['table_no' => 'T008', 'capacity' => 2, 'is_active' => true],
            ['table_no' => 'T009', 'capacity' => 6, 'is_active' => true],
            ['table_no' => 'T010', 'capacity' => 4, 'is_active' => true],
        ];

        foreach ($tables as $table) {
            Table::create($table);
        }
    }
}
