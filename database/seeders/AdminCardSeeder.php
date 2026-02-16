<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminCard;

class AdminCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cards = [
            ['label' => 'Admin Card 01', 'rfid_uid' => '2983705817'],
            ['label' => 'Admin Card 02', 'rfid_uid' => '2980171417'],
            ['label' => 'Admin Card 03', 'rfid_uid' => '2987177225'],
            ['label' => 'Admin Card 04', 'rfid_uid' => '2973073881'],
            ['label' => 'Admin Card 05', 'rfid_uid' => '3013598185'],
            ['label' => 'Admin Card 06', 'rfid_uid' => '3537936698'],
            ['label' => 'Admin Card 07', 'rfid_uid' => '2821509430'],
            ['label' => 'Admin Card 08', 'rfid_uid' => '2989355513'],
            ['label' => 'Admin Card 09', 'rfid_uid' => '2999516633'],
            ['label' => 'Admin Card 10', 'rfid_uid' => '3013818665'],
        ];

        foreach ($cards as $card) {
            AdminCard::updateOrCreate(
                ['rfid_uid' => $card['rfid_uid']],
                [
                    'card_label' => $card['label'],
                    'status' => 'ACTIVE',
                ]
            );
        }
    }
}
