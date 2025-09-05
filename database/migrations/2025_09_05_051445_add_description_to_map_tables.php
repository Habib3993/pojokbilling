<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menambahkan kolom 'description' ke tabel 'map_points'
        Schema::table('map_points', function (Blueprint $table) {
            $table->text('description')->nullable()->after('coordinates');
        });

        // Menambahkan kolom 'description' ke tabel 'map_polylines'
        Schema::table('map_polylines', function (Blueprint $table) {
            $table->text('description')->nullable()->after('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('map_points', function (Blueprint $table) {
            $table->dropColumn('description');
        });
        
        Schema::table('map_polylines', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};