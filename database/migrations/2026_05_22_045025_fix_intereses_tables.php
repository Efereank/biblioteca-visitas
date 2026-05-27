<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar columna nombre a perfiles_interes si no existe
        if (!Schema::hasColumn('perfiles_interes', 'nombre')) {
            Schema::table('perfiles_interes', function (Blueprint $table) {
                $table->string('nombre')->unique()->after('id');
            });
        }

        // Agregar columnas a subcategorias_interes si no existen
        if (!Schema::hasColumn('subcategorias_interes', 'nombre')) {
            Schema::table('subcategorias_interes', function (Blueprint $table) {
                $table->string('nombre')->after('id');
            });
        }

        if (!Schema::hasColumn('subcategorias_interes', 'perfil_interes_id')) {
            Schema::table('subcategorias_interes', function (Blueprint $table) {
                $table->foreignId('perfil_interes_id')->after('nombre')->constrained('perfiles_interes')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
    }
};
