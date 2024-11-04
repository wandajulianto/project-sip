<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konsultasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'balita_id',
        'date',
        'notes',
        'recommendation',
    ];

    // Relasi dengan model Balita
    public function balita()
    {
        return $this->belongsTo(Balita::class);
    }
}
