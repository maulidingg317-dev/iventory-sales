<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'PT Sumber Pangan Nusantara', 'phone' => '0811-0000-0001', 'email' => 'sales@sumberpangan.test'],
            ['name' => 'CV Mitra Minuman',           'phone' => '0811-0000-0002', 'email' => 'order@mitraminuman.test'],
            ['name' => 'UD Cahaya Stationery',       'phone' => '0811-0000-0003', 'email' => 'info@cahayastat.test'],
        ];

        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['name' => $s['name']], $s);
        }
    }
}
