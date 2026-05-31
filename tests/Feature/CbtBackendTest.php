<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\CbtPelanggaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtBackendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::setValue('asesmen_sumatif_active', true);
        Setting::setValue('asesmen_madrasah_active', false);
        Setting::setValue('download_url', 'https://mtsn11majalengka.sch.id/download');
        Setting::setValue('version_android', 'v4.2.3');
        Setting::setValue('url_android', 'https://mtsn11majalengka.sch.id/cbtapp.apk');
        Setting::setValue('version_pc', 'v1.0.0');
        Setting::setValue('url_pc', 'https://mtsn11majalengka.sch.id/cbtapp.exe');
        Setting::setValue('latest_version', '4.2.3');
        Setting::setValue('latest_version_code', 6);
        Setting::setValue('operational_start_date', '2026-01-01 00:00:00');
        Setting::setValue('operational_end_date', '2026-12-31 23:59:59');
        Setting::setValue('daily_start_hour', 0);
        Setting::setValue('daily_start_minute', 0);
        Setting::setValue('daily_end_hour', 23);
        Setting::setValue('daily_end_minute', 59);
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
            'User-Agent' => 'ATADevLabs_CBTAppMTsN11Majalengka',
        ])->get('/');

        $response->assertRedirect(route('verify-security'));
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

    /**
     * Test guest is redirected to login when trying to access version management
     */
    public function test_guest_cannot_access_version_management(): void
    {
        $response = $this->get('/admin/versions');
        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated admin can access version management page
     */
    public function test_admin_can_access_version_management(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $response = $this->actingAs($user)->get('/admin/versions');
        $response->assertStatus(200);
        $response->assertSee('Pembaruan Aplikasi Siswa');
    }

    /**
     * Test admin can update version settings successfully
     */
    public function test_admin_can_update_version_settings(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $response = $this->actingAs($user)->post('/admin/versions/update', [
            'latest_version' => '4.2.4',
            'latest_version_code' => 7,
            'download_url' => 'https://play.google.com/store/apps/details?id=com.mtsn11.cbtapp',
            'release_notes' => 'New security patch',
            'force_update' => 1
        ]);

        $response->assertRedirect(route('admin.versions.index'));
        
        $this->assertEquals('4.2.4', Setting::getValue('latest_version'));
        $this->assertEquals(7, Setting::getValue('latest_version_code'));
        $this->assertTrue(Setting::getValue('force_update'));
    }

    /**
     * Test guest cannot update profile credentials
     */
    public function test_guest_cannot_update_profile(): void
    {
        $response = $this->post('/admin/settings/profile', [
            'name' => 'New Name',
            'username' => 'newusername',
        ]);
        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated admin can update their own profile credentials
     */
    public function test_admin_can_update_profile(): void
    {
        $user = User::create([
            'name' => 'Original Name',
            'username' => 'originaluser',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $response = $this->actingAs($user)->post('/admin/settings/profile', [
            'name' => 'Updated Name',
            'username' => 'updateduser',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        
        $freshUser = $user->fresh();
        $this->assertEquals('Updated Name', $freshUser->name);
        $this->assertEquals('updateduser', $freshUser->username);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword', $freshUser->password));
    }

    /**
     * Test authenticated proctor cannot access admin-only menus/routes
     */
    public function test_proctor_cannot_access_admin_only_routes(): void
    {
        $user = User::create([
            'name' => 'Test Proctor',
            'username' => 'testproctor',
            'password' => bcrypt('password'),
            'role' => 'proktor'
        ]);

        $this->actingAs($user);

        // Try accessing Media Management GET & POST
        $response = $this->get('/admin/media');
        $response->assertStatus(403);

        $response = $this->post('/admin/media/delete');
        $response->assertStatus(403);

        // Try accessing Version Management GET & POST
        $response = $this->get('/admin/versions');
        $response->assertStatus(403);

        $response = $this->post('/admin/versions/update');
        $response->assertStatus(403);

        // Try accessing System Settings Update POST
        $response = $this->post('/admin/settings/update', [
            'operational_start_date' => '2026-01-01 00:00:00',
            'operational_end_date' => '2026-12-31 23:59:59',
            'daily_start_hour' => 7,
            'daily_start_minute' => 0,
            'daily_end_hour' => 12,
            'daily_end_minute' => 0,
            'ad_time' => 5
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test admin can update system settings successfully using new pickers formats
     */
    public function test_admin_can_update_system_settings_with_pickers(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $response = $this->actingAs($user)->post('/admin/settings/update', [
            'operational_start_date' => '2026-05-25T01:00', // datetime-local format
            'operational_end_date' => '2026-06-05T23:59', // datetime-local format
            'daily_start_time' => '07:25', // time format
            'daily_end_time' => '18:30', // time format
            'ad_time' => 5
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertEquals('2026-05-25 01:00:00', Setting::getValue('operational_start_date'));
        $this->assertEquals('2026-06-05 23:59:00', Setting::getValue('operational_end_date'));
        $this->assertEquals(7, Setting::getValue('daily_start_hour'));
        $this->assertEquals(25, Setting::getValue('daily_start_minute'));
        $this->assertEquals(18, Setting::getValue('daily_end_hour'));
        $this->assertEquals(30, Setting::getValue('daily_end_minute'));
    }

    /**
     * Test authenticated proctor can access settings profile page and update their own credentials
     */
    public function test_proctor_can_access_settings_and_update_profile(): void
    {
        $user = User::create([
            'name' => 'Original Proctor',
            'username' => 'origproctor',
            'password' => bcrypt('password'),
            'role' => 'proktor'
        ]);

        $response = $this->actingAs($user)->get('/admin/settings');
        $response->assertStatus(200);
        $response->assertSee('Kredensial Akun Anda');
        $response->assertDontSee('Simpan Semua Setelan Sistem'); // Form settings should be hidden

        $response = $this->post('/admin/settings/profile', [
            'name' => 'Updated Proctor Name',
            'username' => 'updatedproctor',
            'password' => 'proctorpass123',
            'password_confirmation' => 'proctorpass123'
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $freshUser = $user->fresh();
        $this->assertEquals('Updated Proctor Name', $freshUser->name);
        $this->assertEquals('updatedproctor', $freshUser->username);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('proctorpass123', $freshUser->password));
    }

    /**
     * Test admin can delete unbanned violation history successfully
     */
    public function test_admin_can_delete_unbanned_violation_history(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $violation = CbtPelanggaran::create([
            'student_name' => 'Siswa Pelanggar',
            'fcm_token' => 'sample_token',
            'reason' => 'Keluar aplikasi',
            'duration_minutes' => 15,
            'status' => 'UNBANNED'
        ]);

        $response = $this->actingAs($user)->delete("/admin/violations/{$violation->id}");
        $response->assertRedirect(route('admin.violations.index'));
        
        $this->assertDatabaseMissing('cbt_pelanggaran', [
            'id' => $violation->id
        ]);
    }

    /**
     * Test admin cannot delete active banned violations
     */
    public function test_admin_cannot_delete_active_banned_violation(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $violation = CbtPelanggaran::create([
            'student_name' => 'Siswa Terkunci',
            'fcm_token' => 'sample_token_2',
            'reason' => 'Keluar aplikasi',
            'duration_minutes' => 15,
            'status' => 'BANNED'
        ]);

        $response = $this->actingAs($user)->delete("/admin/violations/{$violation->id}");
        $response->assertRedirect(route('admin.violations.index'));
        
        // Assert that the database still has the violation
        $this->assertDatabaseHas('cbt_pelanggaran', [
            'id' => $violation->id,
            'status' => 'BANNED'
        ]);
    }

    /**
     * Test admin can clear all unbanned violations successfully
     */
    public function test_admin_can_clear_all_unbanned_violations(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        CbtPelanggaran::create([
            'student_name' => 'Siswa Pelanggar 1',
            'fcm_token' => 'sample_token_1',
            'reason' => 'Keluar aplikasi',
            'duration_minutes' => 15,
            'status' => 'UNBANNED'
        ]);

        CbtPelanggaran::create([
            'student_name' => 'Siswa Pelanggar 2',
            'fcm_token' => 'sample_token_2',
            'reason' => 'Keluar aplikasi',
            'duration_minutes' => 15,
            'status' => 'UNBANNED'
        ]);

        $activeBan = CbtPelanggaran::create([
            'student_name' => 'Siswa Terkunci',
            'fcm_token' => 'sample_token_3',
            'reason' => 'Keluar aplikasi',
            'duration_minutes' => 15,
            'status' => 'BANNED'
        ]);

        $response = $this->actingAs($user)->delete("/admin/violations/clear-unbanned");
        $response->assertRedirect(route('admin.violations.index', ['status' => 'UNBANNED']));

        // Assert unbanned violations are deleted
        $this->assertDatabaseCount('cbt_pelanggaran', 1);
        $this->assertDatabaseHas('cbt_pelanggaran', [
            'id' => $activeBan->id,
            'status' => 'BANNED'
        ]);
    }
}
