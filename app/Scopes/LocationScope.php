<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class LocationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Hanya terapkan saringan jika pengguna yang login adalah 'admin'
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            
            // Daftar model "global" yang BISA DILIHAT oleh semua admin,
            // jadi TIDAK akan difilter berdasarkan lokasi.
            $globalModels = [
                \App\Models\Router::class,
                \App\Models\Olt::class,
                \App\Models\Vlan::class,
                \App\Models\Package::class,
                \App\Models\IpPool::class,
                \App\Models\GenieAcsServer::class, // Sesuaikan nama model jika berbeda
            ];

            // Dapatkan nama class dari model yang sedang di-query
            $currentModelClass = get_class($model);

            // Terapkan saringan lokasi HANYA JIKA model saat ini BUKAN salah satu dari model global
            if (!in_array($currentModelClass, $globalModels)) {
                $builder->where($model->getTable() . '.location_id', Auth::user()->location_id);
            }
            // Jika modelnya adalah model global, tidak ada filter yang diterapkan,
            // sehingga semua admin (pojok2, pojok3) bisa melihat semua paket, router, dll.
        }
        // Jika yang login adalah superadmin, kondisi di atas tidak terpenuhi,
        // sehingga tidak ada filter sama sekali dan ia bisa melihat semuanya.
    }
}