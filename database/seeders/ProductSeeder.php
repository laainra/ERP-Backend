<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        Product::create([
            'name' => 'Elitech Digital Thermometer DT-100',
            'sku' => 'DT100',
            'description' => 'High-accuracy digital thermometer for clinical and laboratory use.',
        ]);

        Product::create([
            'name' => 'Elitech Blood Pressure Monitor BPM-200',
            'sku' => 'BPM200',
            'description' => 'Automatic blood pressure monitor with Bluetooth connectivity.',
        ]);

        Product::create([
            'name' => 'Elitech Oxygen Concentrator OX-5L',
            'sku' => 'OX5L',
            'description' => '5-liter portable oxygen concentrator for home and hospital use.',
        ]);

        Product::create([
            'name' => 'Elitech Medical Refrigerator MR-150',
            'sku' => 'MR150',
            'description' => 'Smart temperature-controlled refrigerator for vaccine and reagent storage.',
        ]);

        Product::create([
            'name' => 'Elitech Infusion Pump IP-300',
            'sku' => 'IP300',
            'description' => 'Precision infusion pump for continuous intravenous medication delivery.',
        ]);

        Product::create([
            'name' => 'Elitech ECG Machine EC-12',
            'sku' => 'EC12',
            'description' => '12-lead electrocardiogram machine with digital data storage and cloud sync.',
        ]);

        Product::create([
            'name' => 'Elitech Patient Monitor PM-700',
            'sku' => 'PM700',
            'description' => 'Advanced patient vital signs monitor with touchscreen display and data logging.',
        ]);

        Product::create([
            'name' => 'Elitech Sterilizer ST-80',
            'sku' => 'ST80',
            'description' => 'Automatic autoclave sterilizer for surgical tools and medical instruments.',
        ]);

        Product::create([
            'name' => 'Elitech Medical Freezer MF-200',
            'sku' => 'MF200',
            'description' => 'Ultra-low temperature freezer for plasma and biological sample preservation.',
        ]);

        Product::create([
            'name' => 'Elitech Smart IoT Temperature Sensor TMS-500',
            'sku' => 'TMS500',
            'description' => 'IoT-enabled temperature sensor for cold chain monitoring in hospitals.',
        ]);
    }
}
