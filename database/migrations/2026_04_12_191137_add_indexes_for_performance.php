<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            $table->index('fecha_hora_entrada');
            $table->index('fecha_hora_salida');
            $table->index(['visitante_id', 'fecha_hora_entrada']);
        });

        Schema::table('visitantes', function (Blueprint $table) {
            $table->index('cedula');
            $table->index('visitas_count');
        });
    }

    public function down(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            $table->dropIndex(['fecha_hora_entrada']);
            $table->dropIndex(['fecha_hora_salida']);
            $table->dropIndex(['visitante_id', 'fecha_hora_entrada']);
        });

        Schema::table('visitantes', function (Blueprint $table) {
            $table->dropIndex(['cedula']);
            $table->dropIndex(['visitas_count']);
        });
    }
};
