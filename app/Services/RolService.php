<?php

namespace App\Services;

use RuntimeException;

class RolService
{
    private const ROLES_SISTEMA = ['pendiente', 'superadmin', 'cliente', 'empleado'];
    private const ROLES_PERMISOS_FIJOS = ['pendiente', 'superadmin'];
    private const PATRON_SLUG = '/\A[a-z][a-z0-9_]{2,29}\z/';

    public function roles(): array
    {
        $roles = setting('AuthGroups.groups');

        if (! is_array($roles) || $roles === []) {
            $roles = config('AuthGroups')->groups;
        }

        return $roles;
    }

    public function permisos(): array
    {
        $permisos = setting('AuthGroups.permissions');

        if (! is_array($permisos) || $permisos === []) {
            $permisos = config('AuthGroups')->permissions;
        }

        return $permisos;
    }

    public function matriz(): array
    {
        $matriz = setting('AuthGroups.matrix');

        if (! is_array($matriz)) {
            $matriz = config('AuthGroups')->matrix;
        }

        return $matriz;
    }

    public function rol(string $slug): ?array
    {
        $roles = $this->roles();

        return $roles[$this->normalizarSlug($slug)] ?? null;
    }

    public function existe(string $slug): bool
    {
        return $this->rol($slug) !== null;
    }

    public function esSistema(string $slug): bool
    {
        return in_array($this->normalizarSlug($slug), self::ROLES_SISTEMA, true);
    }

    public function puedeEditarPermisos(string $slug): bool
    {
        return ! in_array($this->normalizarSlug($slug), self::ROLES_PERMISOS_FIJOS, true);
    }

    public function rolesAsignables(): array
    {
        $roles = $this->roles();
        unset($roles['pendiente']);

        return $roles;
    }

    public function permisosDe(string $slug): array
    {
        return $this->matriz()[$this->normalizarSlug($slug)] ?? [];
    }

    public function tienePermiso(string $slug, string $permiso): bool
    {
        foreach ($this->permisosDe($slug) as $asignado) {
            if ($asignado === $permiso) {
                return true;
            }

            if (str_ends_with($asignado, '*') && str_starts_with($permiso, substr($asignado, 0, -1))) {
                return true;
            }
        }

        return false;
    }

    public function contarMiembros(string $slug): int
    {
        return db_connect()->table('auth_groups_users g')
            ->join('users u', 'u.id = g.user_id')
            ->where('g.group', $this->normalizarSlug($slug))
            ->where('u.deleted_at', null)
            ->countAllResults();
    }

    public function crear(array $datos): string
    {
        $slug = $this->normalizarSlug((string) ($datos['slug'] ?? ''));
        $rol  = $this->datosRol($datos);
        $roles = $this->roles();

        if (! preg_match(self::PATRON_SLUG, $slug)) {
            throw new RuntimeException('El identificador debe iniciar con una letra y usar solo minúsculas, números o guiones bajos, entre 3 y 30 caracteres.');
        }

        if (in_array($slug, self::ROLES_SISTEMA, true) || array_key_exists($slug, $roles)) {
            throw new RuntimeException('Ese identificador de rol ya está reservado.');
        }

        $roles[$slug] = $rol;

        $matriz           = $this->matriz();
        $matriz[$slug]    = $this->limpiarPermisos($datos['permisos'] ?? []);

        $this->guardar($roles, $matriz);

        return $slug;
    }

    public function actualizar(string $slug, array $datos): void
    {
        $slug  = $this->normalizarSlug($slug);
        $roles = $this->roles();

        if (! array_key_exists($slug, $roles)) {
            throw new RuntimeException('Rol no encontrado.');
        }

        $roles[$slug] = $this->datosRol($datos);

        $matriz = $this->matriz();

        if ($this->puedeEditarPermisos($slug)) {
            $matriz[$slug] = $this->limpiarPermisos($datos['permisos'] ?? []);
        } elseif (! array_key_exists($slug, $matriz)) {
            $matriz[$slug] = [];
        }

        $this->guardar($roles, $matriz);
    }

    public function eliminar(string $slug): void
    {
        $slug = $this->normalizarSlug($slug);

        if (! $this->existe($slug)) {
            throw new RuntimeException('Rol no encontrado.');
        }

        if ($this->esSistema($slug)) {
            throw new RuntimeException('Los roles del sistema no se pueden eliminar.');
        }

        if ($this->contarMiembros($slug) > 0) {
            throw new RuntimeException('El rol todavía tiene usuarios asignados. Retíralos antes de eliminarlo.');
        }

        $roles = $this->roles();
        unset($roles[$slug]);

        $matriz = $this->matriz();
        unset($matriz[$slug]);

        $this->guardar($roles, $matriz);
    }

    private function datosRol(array $datos): array
    {
        $titulo      = trim((string) ($datos['title'] ?? ''));
        $descripcion = trim((string) ($datos['description'] ?? ''));

        if ($titulo === '' || mb_strlen($titulo) < 2 || mb_strlen($titulo) > 100) {
            throw new RuntimeException('El nombre del rol debe tener entre 2 y 100 caracteres.');
        }

        if (mb_strlen($descripcion) > 255) {
            throw new RuntimeException('La descripción no puede superar los 255 caracteres.');
        }

        return [
            'title'       => $titulo,
            'description' => $descripcion,
        ];
    }

    private function limpiarPermisos(mixed $permisos): array
    {
        if (! is_array($permisos)) {
            return [];
        }

        return array_values(array_intersect(
            array_unique(array_map(static fn ($permiso): string => (string) $permiso, $permisos)),
            array_keys($this->permisos()),
        ));
    }

    private function guardar(array $roles, array $matriz): void
    {
        setting('AuthGroups.groups', $roles);
        setting('AuthGroups.matrix', $matriz);
    }

    private function normalizarSlug(string $slug): string
    {
        return strtolower(trim($slug));
    }
}
