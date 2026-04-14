<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitantes', function (Blueprint $table) {
            $table->id();
            $table->string('cedula', 20)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('email', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->enum('genero', ['M', 'F', 'Otro'])->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('institucion', 100)->nullable();
            $table->foreignId('tipo_visitante_id')->constrained('tipos_visitante');
            $table->integer('visitas_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitantes');
    }
};
