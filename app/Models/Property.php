<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = ['name', 'type_id', 'status','category'];

    /**
     * Get the type that this property belongs to.
     */
    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    /**
     * Get the property items associated with this property.
     */
    public function propertyItems()
    {
        return $this->hasMany(PropertyItem::class, 'property_id');
    }
}