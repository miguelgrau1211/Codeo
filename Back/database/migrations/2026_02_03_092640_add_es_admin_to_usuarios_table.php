<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Añadimos el booleano. Por defecto es 'false' (0) por seguridad.
            // 'after' sirve para colocarlo físicamente después de la contraseña.
            $table->boolean('es_admin')->default(false)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Si hacemos rollback, eliminamos la columna
            $table->dropColumn('es_admin');
        });
    }
};