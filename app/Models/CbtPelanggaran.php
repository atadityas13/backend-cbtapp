<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class CbtPelanggaran extends Model
{
    protected $table = 'cbt_pelanggaran';
 
    protected $fillable = [
        'student_name',
        'fcm_token',
        'reason',
        'duration_minutes',
        'device_model',
        'android_version',
        'status',
        'is_hard_lock'
    ];

    protected $casts = [
        'is_hard_lock' => 'boolean',
        'duration_minutes' => 'integer'
    ];
}
