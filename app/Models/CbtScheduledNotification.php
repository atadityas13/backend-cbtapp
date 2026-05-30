<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CbtScheduledNotification extends Model
{
    protected $table = 'cbt_scheduled_notifications';

    protected $fillable = [
        'judul',
        'deskripsi',
        'topik',
        'fcm_token',
        'gambar_url',
        'link',
        'prioritas',
        'custom_sound',
        'category',
        'duration',
        'schedule_time',
        'days_of_week',
        'is_active',
        'last_sent_at',
    ];

    protected $casts = [
        'duration' => 'integer',
        'days_of_week' => 'array',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
    ];
}
