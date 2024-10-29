<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run()
    {
        $clients = [
            [
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
                'phone' => '123-456-7890',
                'address' => '123 Elm St, Springfield',
                'final_price' => 1500.00,
                'remaining_price' => 500.00,
                'cin' => 'CIN001',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'janesmith@example.com',
                'phone' => '234-567-8901',
                'address' => '456 Maple Ave, Springfield',
                'final_price' => 2300.00,
                'remaining_price' => 800.00,
                'cin' => 'CIN002',
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michaelj@example.com',
                'phone' => '345-678-9012',
                'address' => '789 Oak Dr, Springfield',
                'final_price' => 3500.00,
                'remaining_price' => 1200.00,
                'cin' => 'CIN003',
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emilyd@example.com',
                'phone' => '456-789-0123',
                'address' => '101 Pine St, Springfield',
                'final_price' => 2800.00,
                'remaining_price' => 600.00,
                'cin' => 'CIN004',
            ],
            [
                'name' => 'James Brown',
                'email' => 'jamesb@example.com',
                'phone' => '567-890-1234',
                'address' => '202 Cedar Ln, Springfield',
                'final_price' => 1900.00,
                'remaining_price' => 400.00,
                'cin' => 'CIN005',
            ],
            [
                'name' => 'Patricia Wilson',
                'email' => 'patriciaw@example.com',
                'phone' => '678-901-2345',
                'address' => '303 Birch Blvd, Springfield',
                'final_price' => 4100.00,
                'remaining_price' => 1500.00,
                'cin' => 'CIN006',
            ],
            [
                'name' => 'Robert Garcia',
                'email' => 'robertg@example.com',
                'phone' => '789-012-3456',
                'address' => '404 Aspen Cir, Springfield',
                'final_price' => 2200.00,
                'remaining_price' => 900.00,
                'cin' => 'CIN007',
            ],
            [
                'name' => 'Linda Martinez',
                'email' => 'lindam@example.com',
                'phone' => '890-123-4567',
                'address' => '505 Dogwood Dr, Springfield',
                'final_price' => 2700.00,
                'remaining_price' => 500.00,
                'cin' => 'CIN008',
            ],
            [
                'name' => 'William Miller',
                'email' => 'williamm@example.com',
                'phone' => '901-234-5678',
                'address' => '606 Redwood Ln, Springfield',
                'final_price' => 3200.00,
                'remaining_price' => 1300.00,
                'cin' => 'CIN009',
            ],
            [
                'name' => 'Elizabeth Taylor',
                'email' => 'elizabetht@example.com',
                'phone' => '012-345-6789',
                'address' => '707 Spruce St, Springfield',
                'final_price' => 3800.00,
                'remaining_price' => 1100.00,
                'cin' => 'CIN010',
            ],
        ];
        

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
