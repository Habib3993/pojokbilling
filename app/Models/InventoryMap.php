<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToLocation;

class InventoryMap extends BaseModel
{
    use HasFactory, BelongsToLocation;

    protected $fillable = [
        'name',
        'layer_group_id',
        'color',
        'coordinates',
        'description',
        'location_id',
    ];
    public function layerGroup()
    {
        return $this->belongsTo(LayerGroup::class);
    }
}
