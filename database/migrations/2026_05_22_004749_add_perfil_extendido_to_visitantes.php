<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_add_perfil_extendido_to_visitantes.php
public function up(): void
{
    Schema::table('visitantes', function (Blueprint $table) {
        $table->string('tipo_documento', 20)->nullable()->after('id'); // C.I., Pasaporte, etc.
        $table->string('nacionalidad', 50)->nullable();
        $table->string('direccion', 255)->nullable();
        $table->string('municipio', 100)->nullable();
        $table->string('parroquia', 100)->nullable();
        $table->string('ciudad', 100)->nullable();
        $table->string('codigo_postal', 10)->nullable();
        $table->string('grado_instruccion', 50)->nullable();
        $table->string('profesion', 100)->nullable();
        $table->string('situacion_laboral', 50)->nullable();
        $table->string('institucion_educativa_laboral', 150)->nullable();
        $table->string('perfil_interes', 100)->nullable();
        $table->string('subcategoria_interes', 150)->nullable();
        $table->string('formato_preferido', 50)->nullable();
        $table->json('idiomas_interes')->nullable();
        $table->string('discapacidad', 50)->nullable();
        $table->string('necesidades_especiales', 100)->nullable();
        $table->boolean('consentimiento_comunicacion')->default(false);
        $table->text('observaciones')->nullable();
        $table->foreignId('usuario_registrador_id')->nullable()->constrained('users');
        $table->timestamp('fecha_ultima_modificacion')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitantes', function (Blueprint $table) {
            //
        });
    }
};
