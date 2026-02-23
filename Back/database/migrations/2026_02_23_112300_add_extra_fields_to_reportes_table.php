<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->string('email_contacto')->nullable()->after('usuario_id');
            $table->string('tipo')->default('bug')->after('email_contacto');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'critica'])->default('media')->after('estado');
            $table->json('metadatos')->nullable()->after('prioridad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->dropColumn(['email_contacto', 'tipo', 'prioridad', 'metadatos']);
        });
    }
};
