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
        Schema::table('reports', function (Blueprint $table) {
            // Menambah 2 kolom baru setelah kolom longitude
            $table->decimal('remaining_width', 5, 2)->nullable()->after('longitude');
            $table->integer('traffic_volume')->nullable()->after('remaining_width');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Menghapus kolom jika migration di-rollback
            $table->dropColumn(['remaining_width', 'traffic_volume']);
        });
    }
};
