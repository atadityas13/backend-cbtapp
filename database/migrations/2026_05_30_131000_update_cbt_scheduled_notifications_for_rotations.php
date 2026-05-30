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
        Schema::table('cbt_scheduled_notifications', function (Blueprint $table) {
            $table->text('custom_sound')->nullable()->change(); // change type to text to support multiple URLs in JSON array
            $table->integer('last_sent_index')->default(0);
            $table->string('last_sent_sound')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cbt_scheduled_notifications', function (Blueprint $table) {
            $table->string('custom_sound')->nullable()->change();
            $table->dropColumn(['last_sent_index', 'last_sent_sound']);
        });
    }
};
