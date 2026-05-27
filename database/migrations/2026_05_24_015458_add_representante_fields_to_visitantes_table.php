<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('visitantes', function (Blueprint $table) {
        $table->string('representante_nombre')->nullable()->after('observaciones');
        $table->string('representante_cedula')->nullable()->after('representante_nombre');
        $table->string('representante_parentesco')->nullable()->after('representante_cedula');
    });
}

public function down()
{
    Schema::table('visitantes', function (Blueprint $table) {
        $table->dropColumn(['representante_nombre', 'representante_cedula', 'representante_parentesco']);
    });
}
};
