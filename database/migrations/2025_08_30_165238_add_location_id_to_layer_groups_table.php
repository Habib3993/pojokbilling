<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('layer_groups', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->constrained()->after('id');
        });
    }
    public function down(): void {
        Schema::table('layer_groups', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
};