<?php

namespace App\Services;

use App\Models\EmpleadoModel;
use App\Models\EmpresaModel;
use App\Models\UsuariosShieldModel;
use CodeIgniter\Shield\Entities\User;
use RuntimeException;

class UsuarioService
{
    protected UsuariosShieldModel $usuarios;
    protected EmpleadoModel $empleados;
    protected EmpresaModel $empresas;

    public function __construct()
    {
        $this->usuarios  = model(UsuariosShieldModel::class);
        $this->empleados = model(EmpleadoModel::class);
        $this->empresas  = model(EmpresaModel::class);
    }

    public function rolesDisponibles(): array
    {
        return service('rol')->rolesAsignables();
    }

    public function crear(array $datos, array $roles, ?int $empresaId = null): User
    {
        $usuario = new User([
            'username' => strtolower(trim((string) $datos['username'])),
            'email'    => strtolower(trim((string) $datos['email'])),
            'password' => (string) $datos['password'],
            'active'   => (bool) ($datos['active'] ?? true),
        ]);

        if (! $this->usuarios->save($usuario)) {
            throw new RuntimeException('No se pudo guardar el usuario.');
        }

        $usuario = $this->usuarios->findById($this->usuarios->getInsertID());

        if ($usuario === null) {
            throw new RuntimeException('No se pudo recuperar el usuario recién creado.');
        }

        $this->sincronizarRoles($usuario, $roles);
        $this->sincronizarPerfil($usuario, $datos, $this->rolesDe($usuario), $empresaId);

        return $usuario;
    }

    public function actualizar(User $usuario, array $datos, ?array $roles = null, ?int $empresaId = null): User
    {
        $usuario->username = strtolower(trim((string) $datos['username']));
        $usuario->email    = strtolower(trim((string) $datos['email']));
        $usuario->active   = (bool) ($datos['active'] ?? $usuario->active);

        if (! empty($datos['password'])) {
            $usuario->password = (string) $datos['password'];
        }

        if (! $this->usuarios->save($usuario)) {
            throw new RuntimeException('No se pudo actualizar el usuario.');
        }

        if ($roles !== null) {
            $this->sincronizarRoles($usuario, $roles);
        }

        $this->sincronizarPerfil($usuario, $datos, $this->rolesDe($usuario), $empresaId);

        return $usuario;
    }

    public function guardarPerfil(User $usuario, array $datos, int $empresaId): void
    {
        $perfil = $this->perfilDe($usuario);

        $payload = [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuario->id,
            'nombre'     => trim((string) ($datos['nombre'] ?? '')),
            'apellidos'  => trim((string) ($datos['apellidos'] ?? '')),
            'telefono'   => $datos['telefono'] ?? null,
            'estatus'    => (bool) ($datos['active'] ?? true) ? 1 : 0,
        ];

        if ($perfil !== null) {
            if (! $this->empleados->update($perfil['id'], $payload)) {
                throw new RuntimeException('No se pudo actualizar el perfil del usuario.');
            }
        } elseif (! $this->empleados->insert($payload)) {
            throw new RuntimeException('No se pudo crear el perfil del usuario.');
        }
    }

    public function sincronizarRoles(User $usuario, array $roles): void
    {
        $roles = array_map(
            static fn ($rol): string => strtolower(trim((string) $rol)),
            $roles,
        );

        $roles = array_values(array_unique($roles));

        if (in_array('superadmin', $roles, true)) {
            $roles = ['superadmin'];
        }

        $rolesDisponibles   = array_keys($this->rolesDisponibles());
        $rolesDisponibles[] = 'pendiente';

        $roles = array_values(array_intersect($roles, $rolesDisponibles));

        if ($roles === []) {
            throw new RuntimeException('Selecciona al menos un rol válido.');
        }

        $usuario->syncGroups(...$roles);
    }

    public function convertirEnSuperadmin(User $usuario): void
    {
        $this->sincronizarRoles($usuario, ['superadmin']);
        $this->eliminarPerfil($usuario);
    }

    public function requierePerfil(array $roles): bool
    {
        return in_array('cliente', $roles, true) || in_array('empleado', $roles, true);
    }

    private function sincronizarPerfil(User $usuario, array $datos, array $roles, ?int $empresaId): void
    {
        if (in_array('superadmin', $roles, true) || ! $this->requierePerfil($roles)) {
            $this->eliminarPerfil($usuario);

            return;
        }

        if ($empresaId === null) {
            throw new RuntimeException('Los roles Cliente y Empleado requieren una empresa.');
        }

        $this->guardarPerfil($usuario, $datos, $empresaId);
    }

    public function eliminarPerfil(User $usuario): void
    {
        db_connect()->table('empleados')
            ->where('usuario_id', $usuario->id)
            ->delete();
    }

    public function desactivar(User $usuario): void
    {
        $usuario->active = false;
        $this->usuarios->save($usuario);

        db_connect()->table('empleados')
            ->where('usuario_id', $usuario->id)
            ->update(['estatus' => 0]);
    }

    public function activar(User $usuario): void
    {
        $usuario->active = true;
        $this->usuarios->save($usuario);

        db_connect()->table('empleados')
            ->where('usuario_id', $usuario->id)
            ->update(['estatus' => 1]);
    }

    public function eliminar(User $usuario): void
    {
        // Desactivamos el usuario
        $usuario->active = false;
        $this->usuarios->save($usuario);
        
        // Quitamos sus roles
        $usuario->syncGroups();

        // ELIMINAMOS su perfil de la tabla empleados (en lugar de solo desactivarlo)
        // para que no bloquee el borrado de la empresa.
        db_connect()->table('empleados')
            ->where('usuario_id', $usuario->id)
            ->delete(); 

        // Finalmente, borramos al usuario
        $this->usuarios->delete($usuario->id);
    }

    public function restablecerPassword(User $usuario, string $password): void
    {
        $usuario->password = $password;
        $this->usuarios->save($usuario);
    }

    public function perfilDe(User $usuario): ?array
    {
        return $this->empleados
            ->where('usuario_id', $usuario->id)
            ->first();
    }

    public function rolesDe(User $usuario): array
    {
        return $usuario->getGroups() ?? [];
    }

    public function emailDisponible(string $email, ?int $ignorarUsuarioId = null): bool
    {
        $fila = db_connect()->table('auth_identities')
            ->where('type', 'email_password')
            ->where('secret', strtolower(trim($email)))
            ->get()
            ->getRowArray();

        if ($fila === null) {
            return true;
        }

        return $ignorarUsuarioId !== null && (int) $fila['user_id'] === $ignorarUsuarioId;
    }

    public function usernameDisponible(string $username, ?int $ignorarUsuarioId = null): bool
    {
        $fila = db_connect()->table('users')
            ->where('username', strtolower(trim($username)))
            ->get()
            ->getRowArray();

        if ($fila === null) {
            return true;
        }

        return $ignorarUsuarioId !== null && (int) $fila['id'] === $ignorarUsuarioId;
    }

    public function totalEnGrupo(string $grupo, bool $soloActivos = false): int
    {
        $builder = db_connect()->table('auth_groups_users g')
            ->join('users u', 'u.id = g.user_id')
            ->where('g.group', $grupo)
            ->where('u.deleted_at', null);

        if ($soloActivos) {
            $builder->where('u.active', 1);
        }

        return $builder->countAllResults();
    }

    public function esUltimoSuperadmin(User $usuario): bool
    {
        if (! $usuario->inGroup('superadmin') || ! $usuario->active) {
            return false;
        }

        return $this->totalEnGrupo('superadmin', true) <= 1;
    }

    public function empresasActivas(): array
    {
        return $this->empresas
            ->where('estatus', 1)
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }
}
