<?php

namespace App\Models;

use CodeIgniter\Model;

class EmpleadoModel extends Model
{
    protected $table         = 'empleados';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['empresa_id', 'usuario_id', 'nombre', 'apellidos', 'telefono', 'foto', 'estatus'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'empresa_id' => 'required|is_natural_no_zero',
        'usuario_id' => 'required|is_natural_no_zero',
        'nombre'     => 'required|min_length[2]|max_length[100]',
        'apellidos'  => 'required|min_length[2]|max_length[100]',
        'telefono'   => 'permit_empty|max_length[20]',
        'estatus'    => 'permit_empty|in_list[0,1]',
    ];

    public function listarPorEmpresa(int $empresaId): array
    {
        $builder = $this->db->table('empleados e')
            ->select('e.*, u.username, u.active, u.last_active, ai.secret as email')
            ->join('users u', 'u.id = e.usuario_id')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->where('e.empresa_id', $empresaId)
            ->where('u.deleted_at', null)
            ->orderBy('e.nombre', 'ASC')
            ->orderBy('e.apellidos', 'ASC');

        $empleados = $builder->get()->getResultArray();

        if ($empleados === []) {
            return [];
        }

        $grupos = $this->db->table('auth_groups_users')
            ->select('user_id, group')
            ->whereIn('user_id', array_column($empleados, 'usuario_id'))
            ->get()
            ->getResultArray();

        $mapa = [];
        foreach ($grupos as $grupo) {
            $mapa[(int) $grupo['user_id']][] = $grupo['group'];
        }

        foreach ($empleados as &$empleado) {
            $empleado['grupos'] = $mapa[(int) $empleado['usuario_id']] ?? [];
        }
        unset($empleado);

        return $empleados;
    }

    public function listarPaginado(int $empresaId, int $porPagina = 15): array
    {
        $builder = $this->db->table('empleados e')
            ->select('e.*, u.username, u.active, u.last_active, ai.secret as email')
            ->join('users u', 'u.id = e.usuario_id')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->where('e.empresa_id', $empresaId)
            ->where('u.deleted_at', null)
            ->orderBy('e.nombre', 'ASC')
            ->orderBy('e.apellidos', 'ASC');

        $total = $builder->countAllResults(false);
        $page  = service('request')->getGet('page_empleados') ?? 1;
        $items = $builder->limit($porPagina, max(0, ((int) $page - 1) * $porPagina))
            ->get()
            ->getResultArray();

        if ($items !== []) {
            $grupos = $this->db->table('auth_groups_users')
                ->select('user_id, group')
                ->whereIn('user_id', array_column($items, 'usuario_id'))
                ->get()
                ->getResultArray();

            $mapa = [];
            foreach ($grupos as $grupo) {
                $mapa[(int) $grupo['user_id']][] = $grupo['group'];
            }

            foreach ($items as &$empleado) {
                $empleado['grupos'] = $mapa[(int) $empleado['usuario_id']] ?? [];
            }
            unset($empleado);
        }

        return [
            'items' => $items,
            'total' => $total,
            'page'  => (int) $page,
            'per'   => $porPagina,
        ];
    }
}