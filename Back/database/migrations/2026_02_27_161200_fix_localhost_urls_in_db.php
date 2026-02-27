<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $appUrl = config('app.url');
        
        // Corregir las URLs de los logros que tengan localhost
        DB::table('logros')->where('icono_url', 'like', '%localhost%')->get()->each(function ($logro) use ($appUrl) {
            $newUrl = preg_replace('/http:\/\/localhost(\:8000)?/', $appUrl, $logro->icono_url);
            DB::table('logros')->where('id', $logro->id)->update(['icono_url' => $newUrl]);
        });

        // También para los temas si existen
        if (Schema::hasTable('temas')) {
            DB::table('temas')->where('preview_img', 'like', '%localhost%')->get()->each(function ($tema) use ($appUrl) {
                $newUrl = preg_replace('/http:\/\/localhost(\:8000)?/', $appUrl, $tema->preview_img);
                DB::table('temas')->where('id', $tema->id)->update(['preview_img' => $newUrl]);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay vuelta atrás fácil, pero tampoco es destructivo
    }
};
