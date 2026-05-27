<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitantes', function (Blueprint $table) {
            if (!Schema::hasColumn('visitantes', 'fecha_registro')) {
                $table->timestamp('fecha_registro')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitantes', function (Blueprint $table) {
            $table->dropColumn('fecha_registro');
        });
    }
};
