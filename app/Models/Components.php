<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Components extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'components';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'structure',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'structure' => 'array', // Cast le champ JSON en tableau PHP
        'status' => 'string',   // Assure que l'enum est traité comme une chaîne
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
