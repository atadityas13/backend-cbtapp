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
        Schema::create('cbt_pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->string('student_name', 100);
            $table->text('fcm_token');
            $table->text('reason');
            $table->integer('duration_minutes');
            $table->string('device_model', 100)->nullable();
            $table->string('android_version', 20)->nullable();
            $table->enum('status', ['BANNED', 'UNBANNED'])->default('BANNED');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate(); // Laravel timestamps mapping
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_pelanggaran');
    }
};
