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
            $table->integer('points')->default(10)->after('android_id'); // 10 points (Rp 5,000) starting bonus
            $table->boolean('alarm_muted_lifetime')->default(false)->after('points'); // Lifetime silent license
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fcm_registrations', function (Blueprint $table) {
            $table->dropColumn(['points', 'alarm_muted_lifetime']);
        });
    }
};
