<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class LayerGroup extends BaseModel
    {
        use HasFactory;
        protected $fillable = ['name', 'color', 'icon'];

        public function mapPoints()
        {
            return $this->hasMany(MapPoint::class);
        }
    }