<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pertumbuhan extends Model
{
    use HasFactory;

    protected $fillable = ['balita_id', 'date', 'weight', 'height', 'head_circumference', 'notes', 'recorded_by'];

    // Relasi ke model `Balita`
    public function Balita()
    {
        return $this->belongsTo(Balita::class);
    }

    // Relasi ke model `User` untuk pencatat data (kader)
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
