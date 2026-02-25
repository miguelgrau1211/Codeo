<?php

namespace App\Actions\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchUsersAction
{
    public function execute(string $search = null, string $sortBy = 'id', string $sortOrder = 'desc', int $perPage = 8): LengthAwarePaginator
    {
        // 1. Consulta Usuarios Activos
        $activos = DB::table('usuarios')
            ->select('id', 'nickname', 'email', 'nivel_global', DB::raw('1 as active'), 'es_admin');

        if ($search) {
            $activos->where(function ($q) use ($search) {
                $q->where('nickname', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // 2. Consulta Usuarios Desactivados
        $desactivados = DB::table('usuarios_desactivados')
            ->select('usuario_id_original as id', 'nickname', 'email', 'nivel_alcanzado as nivel_global', DB::raw('0 as active'), DB::raw('0 as es_admin'));

        if ($search) {
            $desactivados->where(function ($q) use ($search) {
                $q->where('nickname', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // 3. Unión
        $query = $activos->union($desactivados);

        // 4. Paginación manual para unions en DB::table
        return DB::table(DB::raw("({$query->toSql()}) as combined_users"))
            ->mergeBindings($query)
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }
}
