<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar columna role a users
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'bibliotecario', 'recepcionista'])->default('recepcionista');
        });

        // Tabla pivote sala_user (bibliotecarios asignados a salas)
        Schema::create('sala_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sala_id')->constrained('salas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sala_user');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
