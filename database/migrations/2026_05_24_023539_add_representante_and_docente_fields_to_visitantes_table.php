<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitantes', function (Blueprint $table) {
            // Campos del representante
            if (!Schema::hasColumn('visitantes', 'representante_nombre')) {
                $table->string('representante_nombre')->nullable();
            }
            if (!Schema::hasColumn('visitantes', 'representante_cedula')) {
                $table->string('representante_cedula')->nullable();
            }
            if (!Schema::hasColumn('visitantes', 'representante_parentesco')) {
                $table->string('representante_parentesco')->nullable();
            }
            // Relación con el docente (otro visitante)
            if (!Schema::hasColumn('visitantes', 'docente_id')) {
                $table->foreignId('docente_id')->nullable()->constrained('visitantes')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitantes', function (Blueprint $table) {
            $table->dropForeign(['docente_id']);
            $table->dropColumn(['representante_nombre', 'representante_cedula', 'representante_parentesco', 'docente_id']);
        });
    }
};
