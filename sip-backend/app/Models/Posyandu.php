<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
    /**
     * The attributes that are mass assignable
     * 
     * @var array
     */
    protected $fillable = ['name', 'address'];

    // Relasi dengan model Balita
    public function balitas()
    {
        return $this->hasMany(Balita::class);
    }
}
