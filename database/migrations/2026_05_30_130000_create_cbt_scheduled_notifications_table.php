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
        Schema::create('cbt_scheduled_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('topik')->nullable();
            $table->text('fcm_token')->nullable(); // Target per-siswa jika fcm_token terisi
            $table->string('gambar_url')->nullable();
            $table->string('link')->nullable();
            $table->string('prioritas')->default('high');
            $table->string('custom_sound')->nullable();
            $table->string('category')->default('normal');
            $table->integer('duration')->default(30);
            
            // Scheduling fields
            $table->string('schedule_time'); // format 'H:i' e.g. '07:30'
            $table->text('days_of_week'); // JSON array, e.g., ["Monday", "Tuesday"] or ["ALL"]
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_scheduled_notifications');
    }
};
