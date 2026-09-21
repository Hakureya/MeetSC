<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_form_id',
        'answers',
        'ip_address',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(AttendanceForm::class, 'attendance_form_id');
    }
}
