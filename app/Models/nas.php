<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FilterableByLocation;

class Nas extends BaseModel
{
    use HasFactory, FilterableByLocation;

    protected $table = 'nas'; // Secara eksplisit memberitahu nama tabelnya
    public $timestamps = false; // Tabel 'nas' tidak memiliki kolom created_at/updated_at

    protected $fillable = [
        'nasname',
        'shortname',
        'type',
        'ports',
        'secret',
        'server',
        'community',
        'description',
    ];
}
