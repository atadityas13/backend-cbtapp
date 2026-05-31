<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CbtBantuanProktor extends Model
{
    protected $table = 'cbt_bantuan_proktor';

    protected $fillable = [
        'android_id',
        'username',
        'nama_siswa',
        'pesan_siswa',
        'balasan_proktor',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];
}
