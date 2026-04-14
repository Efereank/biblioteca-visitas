<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propositos_visita', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('color', 20)->default('#17a2b8');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propositos_visita');
    }
};
