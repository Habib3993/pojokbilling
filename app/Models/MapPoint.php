<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToLocation;

class MapPoint extends BaseModel
{
    use HasFactory, BelongsToLocation;

    protected $fillable = [
        'location_id',
        'name',
        'layer_group_id',
        'color',
        'coordinates',
        'description', // <-- PERBAIKAN: Tambahkan ini agar deskripsi bisa disimpan
    ];

    public function layerGroup()
    {
        return $this->belongsTo(LayerGroup::class);
    }
}
