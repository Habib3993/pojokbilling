<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('layer_groups', function (Blueprint $table) {
            // Cek dulu apakah kolomnya ada sebelum menghapus
            if (Schema::hasColumn('layer_groups', 'location_id')) {
                $table->dropForeign(['location_id']);
                $table->dropColumn('location_id');
            }
        });
    }
    // Fungsi down() kita buat untuk mengembalikannya jika perlu
    public function down(): void {
        Schema::table('layer_groups', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->constrained()->after('id');
        });
    }
};