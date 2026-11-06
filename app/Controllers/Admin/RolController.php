<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UsuariosShieldModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class RolController extends BaseController
{
    public function index()
    {
        $servicio = service('rol');
        $roles    = $servicio->roles();

        $conteos = db_connect()->table('auth_groups_users g')
            ->select('g.group, COUNT(*) as total')
            ->join('users u', 'u.id = g.user_id')
            ->where('u.deleted_at', null)
            ->groupBy('g.group')
            ->get()
            ->getResultArray();

        return view('admin/roles/index', [
            'title'          => 'Roles y permisos',
            'roles'          => $roles,
            'permissions'    => $servicio->permisos(),
            'matrix'         => $servicio->matriz(),
            'matrizVisible'  => $this->matrizVisible($roles, $servicio->permisos()),
            'usuariosPorRol' => array_column($conteos, 'total', 'group'),
        ]);
    }

    public function show($slug)
    {
        $slug     = strtolower((string) $slug);
        $servicio = service('rol');
        $rol      = $servicio->rol($slug);

        if ($rol === null) {
            throw PageNotFoundException::forPageNotFound('Rol no encontrado.');
        }

        $usuarios   = $this->usuariosEnGrupo($slug);
        $idsEnGrupo = array_map(static fn (array $usuario): int => (int) $usuario['id'], $usuarios);
        $miembros   = $servicio->contarMiembros($slug);

        return view('admin/roles/show', [
            'title'                => 'Rol: ' . $rol['title'],
            'slug'                 => $slug,
            'rol'                  => $rol,
            'usuarios'             => $usuarios,
            'disponibles'          => $this->usuariosDisponibles($slug, $idsEnGrupo),
            'permisos'             => $servicio->permisosDe($slug),
            'permissions'          => $servicio->permisos(),
            'esSistema'            => $servicio->esSistema($slug),
            'miembros'             => $miembros,
            'puedeEliminar'        => ! $servicio->esSistema($slug) && $miembros === 0,
            'puedeEditarPermisos'  => $servicio->puedeEditarPermisos($slug),
        ]);
    }

    public function new()
    {
        $servicio = service('rol');

        return view('admin/roles/form', [
            'title'                => 'Nuevo rol',
            'slug'                 => null,
            'rol'                  => ['title' => '', 'description' => ''],
            'permisosActuales'     => [],
            'permissions'          => $servicio->permisos(),
            'esSistema'            => false,
            'puedeEditarPermisos'  => true,
        ]);
    }

    public function create()
    {
        try {
            service('rol')->crear([
                'slug'        => (string) $this->request->getPost('slug'),
                'title'       => (string) $this->request->getPost('title'),
                'description' => (string) $this->request->getPost('description'),
                'permisos'    => $this->request->getPost('permisos'),
            ]);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Error al crear rol: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'No se pudo crear el rol.');
        }

        return redirect()->to(site_url('admin/roles'))->with('mensaje', 'Rol creado correctamente.');
    }

    public function edit($slug)
    {
        $slug     = strtolower((string) $slug);
        $servicio = service('rol');
        $rol      = $servicio->rol($slug);

        if ($rol === null) {
            throw PageNotFoundException::forPageNotFound('Rol no encontrado.');
        }

        return view('admin/roles/form', [
            'title'                => 'Editar rol',
            'slug'                 => $slug,
            'rol'                  => $rol,
            'permisosActuales'     => $servicio->permisosDe($slug),
            'permissions'          => $servicio->permisos(),
            'esSistema'            => $servicio->esSistema($slug),
            'puedeEditarPermisos'  => $servicio->puedeEditarPermisos($slug),
        ]);
    }

    public function update($slug)
    {
        try {
            service('rol')->actualizar((string) $slug, [
                'title'       => (string) $this->request->getPost('title'),
                'description' => (string) $this->request->getPost('description'),
                'permisos'    => $this->request->getPost('permisos'),
            ]);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Error al actualizar rol: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'No se pudo actualizar el rol.');
        }

        return redirect()->to(site_url('admin/roles/show/' . strtolower((string) $slug)))
            ->with('mensaje', 'Rol actualizado correctamente.');
    }

    public function delete($slug)
    {
        try {
            service('rol')->eliminar((string) $slug);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('admin/roles'))->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Error al eliminar rol: ' . $e->getMessage());

            return redirect()->to(site_url('admin/roles'))->with('error', 'No se pudo eliminar el rol.');
        }

        return redirect()->to(site_url('admin/roles'))->with('mensaje', 'Rol eliminado correctamente.');
    }

    public function addUser($slug)
    {
        $slug     = strtolower((string) $slug);
        $servicio = service('rol');

        if (! $servicio->existe($slug)) {
            throw PageNotFoundException::forPageNotFound('Rol no encontrado.');
        }

        $usuario = (new UsuariosShieldModel())->find((int) $this->request->getPost('user_id'));

        if ($usuario === null) {
            throw PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        if ($slug === 'superadmin') {
            service('usuario')->convertirEnSuperadmin($usuario);
        } else {
            if ($usuario->inGroup('superadmin')) {
                return redirect()->to(site_url('admin/roles/show/' . $slug))
                    ->with('error', 'El Super Admin no puede compartir otro rol.');
            }

            $usuario->addGroup($slug);
        }

        return redirect()->to(site_url('admin/roles/show/' . $slug))
            ->with('mensaje', 'Rol asignado correctamente.');
    }

    public function removeUser($slug, $userId)
    {
        $slug     = strtolower((string) $slug);
        $servicio = service('rol');

        if (! $servicio->existe($slug)) {
            throw PageNotFoundException::forPageNotFound('Rol no encontrado.');
        }

        $usuario = (new UsuariosShieldModel())->find((int) $userId);

        if ($usuario === null) {
            throw PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        if ($slug === 'superadmin') {
            if ((int) $usuario->id === (int) auth()->id()) {
                return redirect()->to(site_url('admin/roles/show/' . $slug))
                    ->with('error', 'No puedes quitarte el rol de Super Admin.');
            }

            if (service('usuario')->esUltimoSuperadmin($usuario)) {
                return redirect()->to(site_url('admin/roles/show/' . $slug))
                    ->with('error', 'Debe existir al menos un Super Admin.');
            }
        }

        $usuario->removeGroup($slug);

        if ($usuario->getGroups() === []) {
            $usuario->addGroup('pendiente');
        }

        return redirect()->to(site_url('admin/roles/show/' . $slug))
            ->with('mensaje', 'Rol retirado correctamente.');
    }

    private function usuariosEnGrupo(string $slug): array
    {
        $builder = db_connect()->table('auth_groups_users g')
            ->select('u.id, u.username, u.active, u.created_at, ai.secret as email, e.nombre, e.apellidos, emp.nombre as empresa_nombre')
            ->join('users u', 'u.id = g.user_id')
            ->join('auth_identities ai', "ai.user_id = u.id AND ai.type = 'email_password'", 'left')
            ->join('empleados e', 'e.usuario_id = u.id', 'left')
            ->join('empresas emp', 'emp.id = e.empresa_id', 'left')
            ->where('g.group', $slug)
            ->where('u.deleted_at', null);

        if ($slug !== 'superadmin') {
            $builder->where("NOT EXISTS (SELECT 1 FROM auth_groups_users sa WHERE sa.user_id = u.id AND sa.group = 'superadmin')", null, false);
        }

        return $builder
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function usuariosDisponibles(string $slug, array $idsEnGrupo): array
    {
        $usuarios = db_connect()->table('users u')
            ->select('u.id, u.username')
            ->where('u.deleted_at', null)
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();

        if ($usuarios === []) {
            return [];
        }

        $grupos = db_connect()->table('auth_groups_users')
            ->select('user_id, group')
            ->whereIn('user_id', array_column($usuarios, 'id'))
            ->get()
            ->getResultArray();

        $mapa = [];
        foreach ($grupos as $grupo) {
            $mapa[(int) $grupo['user_id']][] = $grupo['group'];
        }

        return array_values(array_filter(
            $usuarios,
            static function (array $usuario) use ($slug, $idsEnGrupo, $mapa): bool {
                $id     = (int) $usuario['id'];
                $grupos = $mapa[$id] ?? [];

                if (in_array($id, $idsEnGrupo, true)) {
                    return false;
                }

                return $slug === 'superadmin' || ! in_array('superadmin', $grupos, true);
            },
        ));
    }

    private function matrizVisible(array $roles, array $permissions): array
    {
        $matriz = [];

        foreach (array_keys($roles) as $slug) {
            foreach (array_keys($permissions) as $permiso) {
                $matriz[$slug][$permiso] = service('rol')->tienePermiso($slug, $permiso);
            }
        }

        return $matriz;
    }
}
