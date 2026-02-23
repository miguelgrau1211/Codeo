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
        Schema::table('logros', function (Blueprint $table) {
            $table->string('requisito_operador')->default('>=')->after('requisito_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logros', function (Blueprint $table) {
            $table->dropColumn('requisito_operador');
        });
    }
};
