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
    Schema::create('routers', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Nama router, cth: Router Pusat
        $table->string('ip_address'); // IP atau domain router
        $table->string('username'); // User API MikroTik
        $table->string('password'); // Password API MikroTik
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routers');
    }
};
