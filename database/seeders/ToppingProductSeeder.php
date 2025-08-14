<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ToppingProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari kategori Toping
        $toppingCategory = Category::where('name', 'Toping')->first();

        if (!$toppingCategory) {
            $this->command->error('Kategori Toping tidak ditemukan!');
            return;
        }

        $toppings = [
            [
                'name' => 'Bakso Ikan',
                'description' => 'Bakso ikan segar untuk seblak',
                'price' => 2000,
                'stock' => 100
            ],
            [
                'name' => 'Bakso Sapi',
                'description' => 'Bakso sapi kenyal dan gurih',
                'price' => 3000,
                'stock' => 80
            ],
            [
                'name' => 'Sosis Ayam',
                'description' => 'Sosis ayam berkualitas untuk topping seblak',
                'price' => 3500,
                'stock' => 60
            ],
            [
                'name' => 'Sosis Keju',
                'description' => 'Sosis dengan isi keju yang lumer',
                'price' => 4000,
                'stock' => 50
            ],
            [
                'name' => 'Cilok',
                'description' => 'Cilok kenyal khas Bandung',
                'price' => 1500,
                'stock' => 120
            ],
            [
                'name' => 'Kerupuk Kulit',
                'description' => 'Kerupuk kulit renyah untuk seblak',
                'price' => 2500,
                'stock' => 90
            ],
            [
                'name' => 'Pangsit Goreng',
                'description' => 'Pangsit goreng renyah',
                'price' => 2000,
                'stock' => 70
            ],
            [
                'name' => 'Tahu Putih',
                'description' => 'Tahu putih segar potong kotak',
                'price' => 1500,
                'stock' => 100
            ],
            [
                'name' => 'Tahu Sumedang',
                'description' => 'Tahu Sumedang goreng krispi',
                'price' => 2000,
                'stock' => 80
            ],
            [
                'name' => 'Tempe Goreng',
                'description' => 'Tempe goreng krispi potong kotak',
                'price' => 1500,
                'stock' => 90
            ],
            [
                'name' => 'Telur Puyuh',
                'description' => 'Telur puyuh rebus untuk topping',
                'price' => 3000,
                'stock' => 60
            ],
            [
                'name' => 'Telur Ayam',
                'description' => 'Telur ayam kampung untuk topping',
                'price' => 5000,
                'stock' => 40
            ],
            [
                'name' => 'Ceker Ayam',
                'description' => 'Ceker ayam empuk dan gurih',
                'price' => 4000,
                'stock' => 50
            ],
            [
                'name' => 'Usus Ayam',
                'description' => 'Usus ayam bersih dan kenyal',
                'price' => 3500,
                'stock' => 45
            ],
            [
                'name' => 'Otak-otak',
                'description' => 'Otak-otak ikan gurih dan lembut',
                'price' => 4500,
                'stock' => 40
            ],
            [
                'name' => 'Dumpling',
                'description' => 'Dumpling ayam mini untuk topping',
                'price' => 3500,
                'stock' => 55
            ],
            [
                'name' => 'Jamur Enoki',
                'description' => 'Jamur enoki segar untuk seblak',
                'price' => 4000,
                'stock' => 30
            ],
            [
                'name' => 'Jamur Tiram',
                'description' => 'Jamur tiram segar potong kecil',
                'price' => 3500,
                'stock' => 35
            ],
            [
                'name' => 'Wortel',
                'description' => 'Wortel potong bulat untuk seblak',
                'price' => 1000,
                'stock' => 100
            ],
            [
                'name' => 'Sawi Hijau',
                'description' => 'Sawi hijau segar untuk seblak',
                'price' => 1500,
                'stock' => 80
            ],
            [
                'name' => 'Keju Mozarella',
                'description' => 'Keju mozarella parut untuk topping',
                'price' => 5000,
                'stock' => 25
            ],
            [
                'name' => 'Keju Cheddar',
                'description' => 'Keju cheddar parut untuk topping',
                'price' => 4500,
                'stock' => 30
            ],
            [
                'name' => 'Mayonaise',
                'description' => 'Mayonaise creamy untuk topping',
                'price' => 2000,
                'stock' => 50
            ],
            [
                'name' => 'Saos Sambal',
                'description' => 'Saos sambal pedas manis',
                'price' => 1500,
                'stock' => 60
            ],
            [
                'name' => 'Bawang Goreng',
                'description' => 'Bawang goreng renyah untuk taburan',
                'price' => 2000,
                'stock' => 70
            ]
        ];

        $this->command->info('Menambahkan produk topping seblak...');

        foreach ($toppings as $topping) {
            Product::create([
                'category_id' => $toppingCategory->id,
                'name' => $topping['name'],
                'description' => $topping['description'],
                'price' => $topping['price'],
                'stock' => $topping['stock'],
                'photo' => null // Bisa ditambahkan nanti
            ]);

            $this->command->line("✓ {$topping['name']} - Rp " . number_format($topping['price'], 0, ',', '.'));
        }

        $this->command->info('Seeder topping seblak selesai!');
    }
}
