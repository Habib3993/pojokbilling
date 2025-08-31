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
        // Fungsi ini hanya akan menambahkan kolom 'setor'
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('setor', 15, 2)->nullable()->default(0)->after('sales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Fungsi ini akan menghapus kolom 'setor' jika di-rollback
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('setor');
        });
    }
};