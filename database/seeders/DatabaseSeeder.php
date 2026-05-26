<?php
 
namespace Database\Seeders;
 
use App\Models\User;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
 
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed initial Super Admin account
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Anzas Tio Aditya',
                'email' => 'admin@cbt.mtsn11majalengka.sch.id',
                'password' => Hash::make('Admin021398'), // Default secure password
                'role' => 'admin',
            ]
        );
 
        // 2. Seed initial configurations from config.json
        $defaultSettings = [
            'admin_display_name'      => 'Anzas Tio Aditya',
            'admin_photo_url'          => 'assets/images/profile.jpg',
            'operational_start_date'  => '2026-05-25 00:00:00',
            'operational_end_date'    => '2026-06-05 23:59:59',
            'daily_start_hour'        => '7',
            'daily_start_minute'      => '25',
            'daily_end_hour'          => '12',
            'daily_end_minute'        => '30',
            'asesmen_sumatif_active'  => true,
            'asesmen_madrasah_active' => false,
            'ad_show'                 => true,
            'ad_image'                => 'https://mtsn11majalengka.sch.id/unggahan/advert_sat2026.jpeg',
            'ad_author'               => 'Riyan Mardiyana, S.Pd.',
            'ad_time'                 => 3,
        ];
 
        foreach ($defaultSettings as $key => $value) {
            Setting::setValue($key, $value);
        }
    }
}
