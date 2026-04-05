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
            ['label' => '22-26001', 'rfid_uid' => '2983705817'],
            ['label' => '22-26002', 'rfid_uid' => '2980171417'],
            ['label' => '22-26003', 'rfid_uid' => '2987177225'],
            ['label' => '22-26004', 'rfid_uid' => '2973073881'],
            ['label' => '22-26005', 'rfid_uid' => '3013598185'],
            ['label' => '22-26006', 'rfid_uid' => '3537936698'],
            ['label' => '22-26007', 'rfid_uid' => '2821509430'],
            ['label' => '22-26008', 'rfid_uid' => '2989355513'],
            ['label' => '22-26009', 'rfid_uid' => '2999516633'],
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
