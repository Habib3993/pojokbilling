<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToLocation;

class MapPolyline extends BaseModel
{
    use HasFactory, BelongsToLocation;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location_id',
        'name',
        'color',
        'path',
        'description', // <-- PERBAIKAN: Tambahkan ini agar deskripsi bisa disimpan
    ];

    // DIHAPUS: Relasi layerGroup() dihapus karena polyline (kabel)
    // tidak terikat pada layer group dalam skema Anda saat ini.
}
