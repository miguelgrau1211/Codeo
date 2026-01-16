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
        Schema::create('usuario_logros_', function (Blueprint $table) {
            $table->foreignId("usuario_id")->constrained("usuarios")->onDelete("cascade");
            $table->foreignId('logro_id')->constrained('logros')->onDelete('cascade');
            $table->timestamp('fecha_desbloqueo')->useCurrent();
            
            // Define que la combinación usuario+logro es única.
            // El usuario 1 solo puede tener el logro 5 una vez.
            $table->primary(['usuario_id', 'logro_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_logros_');
    }
};
