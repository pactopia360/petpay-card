<?php

namespace Database\Seeders;

use App\Models\Repartidor\VehicleCatalogMake;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VehicleCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'motorcycle' => [
                'Honda' => [
                    'Cargo 150',
                    'GL 150 Cargo',
                    'CB 125F',
                    'CB 160F',
                    'CB 190R',
                    'XR 150L',
                    'Dio',
                    'Navi',
                    'Wave',
                    'CGL 125',
                ],
                'Italika' => [
                    'FT 125',
                    'FT 150',
                    'FT 150 G',
                    'FT 200',
                    'DT 125',
                    'DT 150',
                    'DM 200',
                    'DM 250',
                    'WS 150',
                    'WS 175',
                    'Vitalia 125',
                    'Voltium Gravity',
                ],
                'Yamaha' => [
                    'YBR 125',
                    'FZ-S',
                    'FZ 25',
                    'Crypton',
                    'Ray ZR',
                    'NMAX',
                    'XMAX',
                ],
                'Suzuki' => [
                    'EN 125',
                    'Gixxer 150',
                    'Gixxer 250',
                    'Burgman',
                    'AX4',
                ],
                'Bajaj' => [
                    'Boxer 150',
                    'Pulsar NS 125',
                    'Pulsar NS 160',
                    'Pulsar NS 200',
                    'Pulsar N 250',
                    'Dominar 250',
                ],
                'Vento' => [
                    'Workman 150',
                    'Lithium',
                    'Crossmax',
                    'Rocketman',
                    'Nitrox',
                ],
                'TVS' => [
                    'HLX 150',
                    'Raider 125',
                    'Apache RTR 160',
                    'Apache RTR 200',
                    'Ntorq 125',
                ],
                'KTM' => [
                    'Duke 200',
                    'Duke 250',
                    'Duke 390',
                ],
                'BMW Motorrad' => [
                    'G 310 R',
                    'G 310 GS',
                ],
                'Otra marca' => [
                    'Otro modelo',
                ],
            ],

            'car' => [
                'Nissan' => [
                    'March',
                    'Versa',
                    'Sentra',
                    'Tiida',
                    'Tsuru',
                    'V-Drive',
                    'Kicks',
                ],
                'Chevrolet' => [
                    'Aveo',
                    'Beat',
                    'Spark',
                    'Onix',
                    'Cavalier',
                    'Cruze',
                    'Tracker',
                ],
                'Volkswagen' => [
                    'Polo',
                    'Vento',
                    'Virtus',
                    'Jetta',
                    'Gol',
                    'Taos',
                    'T-Cross',
                ],
                'Toyota' => [
                    'Yaris',
                    'Corolla',
                    'Prius',
                    'Avanza',
                    'Raize',
                    'RAV4',
                ],
                'Kia' => [
                    'Rio',
                    'K3',
                    'Forte',
                    'Soul',
                    'Seltos',
                ],
                'Hyundai' => [
                    'Grand i10',
                    'HB20',
                    'Accent',
                    'Elantra',
                    'Creta',
                ],
                'Mazda' => [
                    'Mazda 2',
                    'Mazda 3',
                    'CX-3',
                    'CX-30',
                    'CX-5',
                ],
                'Honda' => [
                    'City',
                    'Civic',
                    'Accord',
                    'BR-V',
                    'HR-V',
                    'CR-V',
                ],
                'Ford' => [
                    'Fiesta',
                    'Focus',
                    'Figo',
                    'EcoSport',
                    'Escape',
                ],
                'Renault' => [
                    'Kwid',
                    'Logan',
                    'Stepway',
                    'Duster',
                ],
                'MG' => [
                    'MG3',
                    'MG5',
                    'ZS',
                    'GT',
                ],
                'Otra marca' => [
                    'Otro modelo',
                ],
            ],

            'van' => [
                'Nissan' => [
                    'NP300',
                    'Urvan',
                    'NV350',
                    'Frontier',
                ],
                'Chevrolet' => [
                    'Tornado Van',
                    'S10',
                    'Silverado',
                    'Express',
                ],
                'Ram' => [
                    'Promaster Rapid',
                    'Promaster',
                    '700',
                    '1200',
                ],
                'Ford' => [
                    'Transit',
                    'Transit Courier',
                    'Ranger',
                    'F-150',
                ],
                'Volkswagen' => [
                    'Caddy',
                    'Transporter',
                    'Crafter',
                    'Saveiro',
                    'Amarok',
                ],
                'Toyota' => [
                    'Hiace',
                    'Hilux',
                ],
                'Peugeot' => [
                    'Partner',
                    'Expert',
                    'Manager',
                ],
                'Renault' => [
                    'Kangoo',
                    'Master',
                    'Oroch',
                ],
                'Otra marca' => [
                    'Otro modelo',
                ],
            ],

            'bicycle' => [
                'Mercurio' => [
                    'Montaña',
                    'Urbana',
                    'Ruta',
                    'Eléctrica',
                ],
                'Benotto' => [
                    'Montaña',
                    'Urbana',
                    'Ruta',
                    'Eléctrica',
                ],
                'Alubike' => [
                    'Montaña',
                    'Urbana',
                    'Ruta',
                ],
                'Trek' => [
                    'Marlin',
                    'Dual Sport',
                    'FX',
                ],
                'Specialized' => [
                    'Rockhopper',
                    'Sirrus',
                    'Turbo',
                ],
                'Giant' => [
                    'Talon',
                    'Escape',
                    'Explore E+',
                ],
                'Otra marca' => [
                    'Otro modelo',
                ],
            ],

            'other' => [
                'Otra marca' => [
                    'Otro modelo',
                ],
            ],
        ];

        foreach ($catalog as $vehicleType => $makes) {
            $makeOrder = 0;

            foreach ($makes as $makeName => $models) {
                $make = VehicleCatalogMake::query()->updateOrCreate(
                    [
                        'vehicle_type' => $vehicleType,
                        'slug' => Str::slug($makeName),
                    ],
                    [
                        'name' => $makeName,
                        'is_active' => true,
                        'sort_order' => $makeOrder++,
                    ]
                );

                foreach (array_values($models) as $modelOrder => $modelName) {
                    $make->models()->updateOrCreate(
                        [
                            'slug' => Str::slug($modelName),
                        ],
                        [
                            'name' => $modelName,
                            'is_active' => true,
                            'sort_order' => $modelOrder,
                        ]
                    );
                }
            }
        }
    }
}
