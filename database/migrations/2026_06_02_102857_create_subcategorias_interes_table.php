<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subcategorias_interes')) {
            Schema::create('subcategorias_interes', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->foreignId('perfil_interes_id')->constrained('perfiles_interes');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subcategorias_interes');
    }
};
