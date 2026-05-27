<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciudades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('municipio_id')->constrained('municipios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciudades');
    }
};
