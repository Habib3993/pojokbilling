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
        Schema::table('layer_groups', function (Blueprint $table) {
            // Tambahkan kolom color dan icon yang hilang
            if (!Schema::hasColumn('layer_groups', 'color')) {
                $table->string('color')->nullable()->after('name');
            }
            if (!Schema::hasColumn('layer_groups', 'icon')) {
                $table->string('icon')->nullable()->after('color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layer_groups', function (Blueprint $table) {
            $table->dropColumn(['color', 'icon']);
        });
    }
};
