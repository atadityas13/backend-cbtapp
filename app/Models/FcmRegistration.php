<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class FcmRegistration extends Model
{
    protected $table = 'fcm_registrations';
 
    protected $fillable = [
        'fcm_token',
        'full_name',
        'topic',
        'android_id',
        'points',
        'alarm_muted_lifetime'
    ];
 
    protected $casts = [
        'points' => 'integer',
        'alarm_muted_lifetime' => 'boolean'
    ];
 
    // Map legacy registration_timestamp column to CREATED_AT
    const CREATED_AT = 'registration_timestamp';
}
