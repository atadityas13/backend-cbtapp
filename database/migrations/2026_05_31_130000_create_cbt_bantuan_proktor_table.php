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
        Schema::create('cbt_bantuan_proktor', function (Blueprint $table) {
            $table->id();
            $table->string('android_id', 50);
            $table->string('username', 50);
            $table->string('nama_siswa', 100);
            $table->text('pesan_siswa');
            $table->text('balasan_proktor')->nullable();
            $table->enum('status', ['PENDING', 'ANSWERED', 'RESOLVED'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_bantuan_proktor');
    }
};
