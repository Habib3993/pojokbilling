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
        Schema::create('genie_acs_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama server, cth: GenieACS Pusat
            $table->string('url'); // URL, cth: http://192.168.10.253:3000
            $table->string('username');
            $table->text('password'); // Dibuat text agar bisa dienkripsi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genie_acs_servers');
    }
};
