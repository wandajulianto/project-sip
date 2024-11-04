<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balita extends Model
{
    use HasFactory;

    protected $table = 'balitas';

    /**
     * Kolom-kolom yang bisa diisi massal.
     */
    protected $fillable = [
        'posyandu_id',
        'name',
        'birth-date',
        'gender',
        'orang_tua_id',
        'birth_height',
        'birth_weight',
    ];

    /**
     * Relasi dengan model Posyandu.
     * Setiap balita terkait dengan satu posyandu.
     */
    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class);
    }

    // Relasi dengan model User
    public function orangTua() 
    {
        return $this->belongsTo(User::class, 'orang_tua_id');
    }

    // Relasi dengan model Pertumbuhan
    public function pertumbuhan()
    {
        return $this->hasMany(Pertumbuhan::class);
    }

    // Relasi dengan model Imunisasi
    public function imunisasi()
    {
        return $this->hasMany(Imunisasi::class);
    }

    // Relasi dengan model Konsultasi
    public function konsultasi()
    {
        return $this->hasMany(Konsultasi::class);
    }
}
