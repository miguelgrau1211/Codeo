<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('usuarios_desactivados', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('usuario_id_original'); 
        $table->string('nickname');
        $table->string('email');
        $table->integer('nivel_alcanzado');
        $table->text('motivo')->nullable();
        $table->timestamp('fecha_desactivacion')->useCurrent();
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('usuarios_desactivados');
    }
};