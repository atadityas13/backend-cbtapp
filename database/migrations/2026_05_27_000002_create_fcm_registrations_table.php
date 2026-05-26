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
        Schema::create('fcm_registrations', function (Blueprint $table) {
            $table->id();
            $table->text('fcm_token'); // fcm_token is long, text is better or varchar(500)
            $table->string('full_name');
            $table->string('topic')->default('cbt_notif');
            $table->string('android_id')->nullable();
            $table->timestamp('registration_timestamp')->useCurrent();
            $table->timestamps(); // keeps standard Laravel timestamps alongside
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_registrations');
    }
};
