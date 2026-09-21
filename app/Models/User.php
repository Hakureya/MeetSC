<?php

namespace App\Models;

// Notifiable & MustVerifyEmail sudah tersedia bawaan Laravel
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'divisi',
        'jabatan',
        'role',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return strtolower($this->role) === 'admin';
    }

    public function roomReservations()
    {
        return $this->hasMany(RoomReservation::class);
    }

    public function zoomReservations()
    {
        return $this->hasMany(ZoomReservation::class);
    }

    public function attendanceForms()
    {
        return $this->hasMany(AttendanceForm::class);
    }
}
