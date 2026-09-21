<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun admin
        $admin = User::create([
            'name' => 'Arif Budiono',
            'email' => 'admin@plnsc.co.id',
            'password' => Hash::make('password'),
            'nip' => '2331165',
            'divisi' => 'Renbis',
            'jabatan' => 'Anak Magang',
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        // 8 ruang individu (yang tampil di landing page) + 2 ruang gabungan (tidak tampil di landing).
        $individualRooms = [
            ['name' => 'Ruang Solution', 'floor' => 1, 'min' => 6, 'max' => 6, 'image' => 'rooms/Solution.jpeg'],
            ['name' => 'Ruang Chain', 'floor' => 1, 'min' => 6, 'max' => 6, 'image' => 'rooms/Chain.jpeg'],
            ['name' => 'Ruang Supply', 'floor' => 1, 'min' => 10, 'max' => 12, 'image' => 'rooms/Supply.jpeg'],
            ['name' => 'Ruang Adaptif', 'floor' => 2, 'min' => 7, 'max' => 7, 'image' => 'rooms/Adaptif.jpeg'],
            ['name' => 'Ruang Kompeten', 'floor' => 2, 'min' => 7, 'max' => 7, 'image' => 'rooms/Kompeten.jpeg'],
            ['name' => 'Ruang Amanah', 'floor' => 2, 'min' => 7, 'max' => 7, 'image' => 'rooms/Amanah.jpeg'],
            ['name' => 'Ruang Prima', 'floor' => 3, 'min' => 7, 'max' => 8, 'image' => 'rooms/Prima.jpeg'],
            ['name' => 'Ruang Layanan', 'floor' => 3, 'min' => 10, 'max' => 10, 'image' => 'rooms/Layanan.jpeg'],
        ];

        $rooms = collect();
        foreach ($individualRooms as $data) {
            $rooms->put($data['name'], Room::create([
                'name' => $data['name'],
                'floor' => $data['floor'],
                'capacity_min' => $data['min'],
                'capacity_max' => $data['max'],
                'is_combined' => false,
                'image' => $data['image'],
            ]));
        }

        // Ruang gabungan: dibentuk dari 2 ruang individu di lantai yang sama.
        // Tidak muncul di landing page, tapi tersimpan untuk pengecekan bentrok jadwal nanti.
        Room::create([
            'name' => 'Ruang Adaptif + Kompeten',
            'floor' => 2,
            'capacity_min' => 13,
            'capacity_max' => 13,
            'is_combined' => true,
            'combined_room_ids' => [$rooms['Ruang Adaptif']->id, $rooms['Ruang Kompeten']->id],
            'image' => 'rooms/AdaptifKompeten.jpeg',
        ]);

        Room::create([
            'name' => 'Ruang Prima + Layanan',
            'floor' => 3,
            'capacity_min' => 17,
            'capacity_max' => 17,
            'is_combined' => true,
            'combined_room_ids' => [$rooms['Ruang Prima']->id, $rooms['Ruang Layanan']->id],
            'image' => 'rooms/PrimaLayanan.jpeg',
        ]);
    }
}
