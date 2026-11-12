<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->decimal('damage_length', 8, 2)->nullable()->after('geometry'); // Panjang (m)
            $table->decimal('damage_width', 8, 2)->nullable()->after('damage_length'); // Lebar (m)
            $table->decimal('damage_depth', 8, 2)->nullable()->after('damage_width'); // Kedalaman (cm)
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['damage_length', 'damage_width', 'damage_depth']);
        });
    }
};
