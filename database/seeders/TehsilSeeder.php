<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// Seeds tehsils for all 5 BISE Sukkur districts
class TehsilSeeder extends Seeder
{
    public function run(): void
    {
        $districts = \App\Models\District::all()->keyBy('code');

        if ($districts->isEmpty()) {
            return;
        }

        $tehsils = [
            'SUK' => [
                ['name' => 'Sukkur', 'code' => '1'],
                ['name' => 'Rohri', 'code' => '2'],
                ['name' => 'Pano Aqil', 'code' => '3'],
                ['name' => 'Salehpat', 'code' => '4'],
            ],
            'KHP' => [
                ['name' => 'Khairpur', 'code' => '1'],
                ['name' => 'Kot Diji', 'code' => '2'],
                ['name' => 'Kingri', 'code' => '3'],
                ['name' => 'Sobhodero', 'code' => '4'],
                ['name' => 'Gambat', 'code' => '5'],
                ['name' => 'Thari Mirwah', 'code' => '6'],
                ['name' => 'Faiz Ganj', 'code' => '7'],
                ['name' => 'Nara', 'code' => '8'],
            ],
            'NF' => [
                ['name' => 'Naushahro Feroze', 'code' => '1'],
                ['name' => 'Bhiria', 'code' => '2'],
                ['name' => 'Moro', 'code' => '3'],
                ['name' => 'Kandiaro', 'code' => '4'],
                ['name' => 'Mehrabpur', 'code' => '5'],
            ],
            'GHK' => [
                ['name' => 'Ghotki', 'code' => '1'],
                ['name' => 'Ubauro', 'code' => '2'],
                ['name' => 'Daharki', 'code' => '3'],
                ['name' => 'Mirpur Mathelo', 'code' => '4'],
                ['name' => 'Khangarh', 'code' => '5'],
            ],
            'SKP' => [
                ['name' => 'Shikarpur', 'code' => '1'],
                ['name' => 'Lakhi Ghulam Shah', 'code' => '2'],
                ['name' => 'Khanpur', 'code' => '3'],
                ['name' => 'Garhi Yasin', 'code' => '4'],
            ],
        ];

        foreach ($tehsils as $distCode => $list) {
            if ($district = $districts->get($distCode)) {
                foreach ($list as $t) {
                    \App\Models\Tehsil::firstOrCreate(
                        ['district_id' => $district->id, 'name' => $t['name']],
                        ['code' => $t['code']]
                    );
                }
            }
        }
    }
}
