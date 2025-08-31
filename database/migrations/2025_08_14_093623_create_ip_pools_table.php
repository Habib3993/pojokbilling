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
    Schema::create('ip_pools', function (Blueprint $table) {
        $table->id();
        $table->string('pool_name')->unique(); // Nama pool di MikroTik
        $table->string('ranges'); // Contoh: 192.168.100.2-192.168.100.254
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_pools');
    }
};
