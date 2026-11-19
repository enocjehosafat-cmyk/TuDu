<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UsuariosShieldModel extends ShieldUserModel
{
    public function listarConPerfil(?string $busqueda = null): array
    {
        $builder = $this->db->table('users u')
            ->select('u.id, u.username, u.active, u.created_at, u.last_active, ai.secret as email, e.id as empleado_id, e.empresa_id, e.nombre, e.apellidos, e.telefono, emp.nombre as empresa_nombre')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('empleados e', 'e.usuario_id = u.id', 'left')
            ->join('empresas emp', 'emp.id = e.empresa_id', 'left')
            ->where('u.deleted_at', null)
            ->orderBy('u.id', 'DESC');

        if ($busqueda !== null && trim($busqueda) !== '') {
            $busqueda = trim($busqueda);
            $builder->groupStart()
                ->like('u.username', $busqueda)
                ->orLike('ai.secret', $busqueda)
                ->orLike('e.nombre', $busqueda)
                ->orLike('e.apellidos', $busqueda)
                ->orLike('emp.nombre', $busqueda)
                ->groupEnd();
        }

        $usuarios = $builder->get()->getResultArray();

        if ($usuarios === []) {
            return [];
        }

        $grupos = $this->db->table('auth_groups_users')
            ->select('user_id, group')
            ->whereIn('user_id', array_column($usuarios, 'id'))
            ->get()
            ->getResultArray();

        $mapa = [];
        foreach ($grupos as $grupo) {
            $mapa[(int) $grupo['user_id']][] = $grupo['group'];
        }

        foreach ($usuarios as &$usuario) {
            $usuario['grupos'] = $mapa[(int) $usuario['id']] ?? [];
        }
        unset($usuario);

        return $usuarios;
    }
}
