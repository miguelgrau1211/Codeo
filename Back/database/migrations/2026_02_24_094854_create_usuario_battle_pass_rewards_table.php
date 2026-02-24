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
        Schema::create('usuario_battle_pass_rewards', function (Blueprint $row) {
            $row->id();
            $row->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $row->integer('nivel_recompensa');
            $row->timestamp('granted_at')->useCurrent();
            
            $row->unique(['usuario_id', 'nivel_recompensa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_battle_pass_rewards');
    }
};
