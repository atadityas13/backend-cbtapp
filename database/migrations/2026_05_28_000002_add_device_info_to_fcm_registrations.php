<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fcm_registrations', function (Blueprint $table) {
            $table->string('device_model', 100)->nullable()->after('android_id');
            $table->string('android_version', 20)->nullable()->after('device_model');
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fcm_registrations', function (Blueprint $table) {
            $table->dropColumn(['device_model', 'android_version']);
        });
    }
};
