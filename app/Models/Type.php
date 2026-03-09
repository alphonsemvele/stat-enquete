<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    // protected $table = "types";

    protected $fillable = ['name', 'value', 'status'];

    /**
     * Get the properties associated with this type.
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'type_id');
    }
}