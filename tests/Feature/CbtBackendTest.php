<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtBackendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed default parameters for setting
        Setting::setValue('asesmen_sumatif_active', true);
        Setting::setValue('asesmen_madrasah_active', false);
        Setting::setValue('download_url', 'https://mtsn11majalengka.sch.id/download');
        Setting::setValue('version_android', 'v4.2.3');
        Setting::setValue('url_android', 'https://mtsn11majalengka.sch.id/cbtapp.apk');
        Setting::setValue('version_pc', 'v1.0.0');
        Setting::setValue('url_pc', 'https://mtsn11majalengka.sch.id/cbtapp.exe');
    }

    /**
     * Test User-Agent blocking on landing page
     */
    public function test_landing_page_blocks_unauthorized_user_agents(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get('/');

        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');
    }

    /**
     * Test User-Agent allowance on landing page
     */
    public function test_landing_page_allows_authorized_user_agents(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'AdityAs13xCBTAppMTsN11Majalengka_V423',
        ])->get('/');

        $response->assertStatus(200);
        $response->assertSee('PORTAL CBT ONLINE');
    }

    /**
     * Test check update API response JSON
     */
    public function test_check_update_api_returns_expected_json(): void
    {
        $response = $this->postJson('/api/check_update.php');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'latest_version',
            'release_notes',
            'download_url',
            'force_update'
        ]);
        
        $response->assertJson([
            'latest_version' => '4.2.3'
        ]);
    }
}
