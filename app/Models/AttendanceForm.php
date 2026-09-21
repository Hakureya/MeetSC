<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'uuid',
        'judul',
        'pic',
        'divisi_pic',
        'tempat',
        'rapat_pertemuan',
        'fields',
        'expires_at',
    ];

    protected $casts = [
        'fields' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responses()
    {
        return $this->hasMany(AttendanceResponse::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->greaterThan($this->expires_at);
    }
}