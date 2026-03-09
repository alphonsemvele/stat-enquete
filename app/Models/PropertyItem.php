<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyItem extends Model
{
    protected $fillable = ["value","property_id","status"];

      public function properties()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
    
}
