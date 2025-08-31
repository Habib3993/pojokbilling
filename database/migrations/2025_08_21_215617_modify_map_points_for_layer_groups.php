<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('map_points', function (Blueprint $table) {
            // Tambahkan foreign key ke layer_groups
            $table->foreignId('layer_group_id')->nullable()->constrained('layer_groups')->after('location_id');
            // Hapus kolom 'type' yang lama
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('map_points', function (Blueprint $table) {
            $table->string('type')->after('location_id');
            $table->dropForeign(['layer_group_id']);
            $table->dropColumn('layer_group_id');
        });
    }
};
