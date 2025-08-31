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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lokasi');
            $table->string('paket')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('phone')->unique();
            $table->string('server')->nullable();
            $table->string('distribusi')->nullable();
            $table->string('odp')->nullable();
            $table->date('subscription_date')->nullable();
            $table->string('sales')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
