<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmpresaModel;
use App\Models\UsuariosShieldModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Shield\Entities\User;

class UsuarioController extends BaseController
{
    public function index()
    {
        $busqueda = trim((string) ($this->request->getGet('q') ?? ''));

        return view('admin/usuarios/index', [
            'title'     => 'Gestión de usuarios',
            'usuarios'  => (new UsuariosShieldModel())->listarConPerfil($busqueda),
            'roles'     => service('rol')->roles(),
            'busqueda'  => $busqueda,
        ]);
    }

    public function new()
    {
        return view('admin/usuarios/form', [
            'title'             => 'Nuevo usuario',
            'usuario'           => null,
            'perfil'            => null,
            'rolesActuales'     => [],
            'rolesDisponibles'  => service('usuario')->rolesDisponibles(),
            'empresas'          => service('usuario')->empresasActivas(),
        ]);
    }

    public function create()
    {
        if (! $this->validarReglas()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roles = $this->rolesFormulario();

        if ($roles === []) {
            return redirect()->back()->withInput()->with('error', 'Selecciona al menos un rol.');
        }

        if (in_array('superadmin', $roles, true) && count($roles) > 1) {
            return redirect()->back()->withInput()->with('error', 'El Super Admin no puede combinarse con otros roles.');
        }

        $empresaId = in_array('superadmin', $roles, true) ? null : $this->empresaFormulario();

        if ($this->rolesRequierenEmpresa($roles) && $empresaId === null) {
            return redirect()->back()->withInput()->with('error', 'Los roles Cliente y Empleado requieren una empresa.');
        }

        if ($empresaId !== null && (new EmpresaModel())->find($empresaId) === null) {
            return redirect()->back()->withInput()->with('error', 'La empresa seleccionada no existe.');
        }

        $datos = $this->datosFormulario();

        if (! service('usuario')->emailDisponible($datos['email'])) {
            return redirect()->back()->withInput()->with('error', 'Ese correo ya está registrado.');
        }

        if (! service('usuario')->usernameDisponible($datos['username'])) {
            return redirect()->back()->withInput()->with('error', 'Ese nombre de usuario ya está registrado.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            service('usuario')->crear($datos, $roles, $empresaId);
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al crear usuario: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'No se pudo crear el usuario. Revisa los datos e intenta de nuevo.');
        }

        return redirect()->to(site_url('admin/usuarios'))->with('mensaje', 'Usuario creado correctamente.');
    }

    public function edit($id)
    {
        $usuario = (new UsuariosShieldModel())->find($id);

        if ($usuario === null) {
            throw PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        return view('admin/usuarios/form', [
            'title'             => 'Editar usuario',
            'usuario'           => $usuario,
            'perfil'            => service('usuario')->perfilDe($usuario),
            'rolesActuales'     => service('usuario')->rolesDe($usuario),
            'rolesDisponibles'  => service('usuario')->rolesDisponibles(),
            'empresas'          => service('usuario')->empresasActivas(),
        ]);
    }

    public function update($id)
    {
        $usuario = (new UsuariosShieldModel())->find($id);

        if ($usuario === null) {
            throw PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        if (! $this->validarReglas((int) $usuario->id)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roles = $this->rolesFormulario();

        if ($roles === []) {
            return redirect()->back()->withInput()->with('error', 'Selecciona al menos un rol.');
        }

        if (in_array('superadmin', $roles, true) && count($roles) > 1) {
            return redirect()->back()->withInput()->with('error', 'El Super Admin no puede combinarse con otros roles.');
        }

        $activo    = (bool) $this->request->getPost('active');
        $empresaId = in_array('superadmin', $roles, true) ? null : $this->empresaFormulario();
        $perfil    = service('usuario')->perfilDe($usuario);

        if ($empresaId === null && $perfil !== null && $this->rolesRequierenEmpresa($roles)) {
            $empresaId = (int) $perfil['empresa_id'];
        }

        if ($this->rolesRequierenEmpresa($roles) && $empresaId === null) {
            return redirect()->back()->withInput()->with('error', 'Los roles Cliente y Empleado requieren una empresa.');
        }

        if ($empresaId !== null && (new EmpresaModel())->find($empresaId) === null) {
            return redirect()->back()->withInput()->with('error', 'La empresa seleccionada no existe.');
        }

        $esMismoUsuario = (int) $usuario->id === (int) auth()->id();

        if ($esMismoUsuario && ! $activo) {
            return redirect()->back()->withInput()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        if ($esMismoUsuario && $usuario->inGroup('superadmin') && ! in_array('superadmin', $roles, true)) {
            return redirect()->back()->withInput()->with('error', 'No puedes quitarte el rol de Super Admin.');
        }

        $pierdeAccesoSuperadmin = $usuario->inGroup('superadmin')
            && (! $activo || ! in_array('superadmin', $roles, true));

        if ($pierdeAccesoSuperadmin && service('usuario')->esUltimoSuperadmin($usuario)) {
            return redirect()->back()->withInput()->with('error', 'Debe existir al menos un Super Admin activo.');
        }

        $datos = $this->datosFormulario();

        if (! service('usuario')->emailDisponible($datos['email'], (int) $usuario->id)) {
            return redirect()->back()->withInput()->with('error', 'Ese correo ya está registrado por otro usuario.');
        }

        if (! service('usuario')->usernameDisponible($datos['username'], (int) $usuario->id)) {
            return redirect()->back()->withInput()->with('error', 'Ese nombre de usuario ya está registrado por otro usuario.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            service('usuario')->actualizar($usuario, $datos, $roles, $empresaId);
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al actualizar usuario: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'No se pudo actualizar el usuario. Revisa los datos e intenta de nuevo.');
        }

        return redirect()->to(site_url('admin/usuarios'))->with('mensaje', 'Usuario actualizado correctamente.');
    }

    public function delete($id)
    {
        $usuario = (new UsuariosShieldModel())->find($id);

        if ($usuario === null) {
            throw PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        if ((int) $usuario->id === (int) auth()->id()) {
            return redirect()->to(site_url('admin/usuarios'))->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($usuario->inGroup('superadmin') && service('usuario')->esUltimoSuperadmin($usuario)) {
            return redirect()->to(site_url('admin/usuarios'))->with('error', 'No puedes eliminar al último Super Admin.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            service('usuario')->eliminar($usuario);
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al eliminar usuario: ' . $e->getMessage());

            return redirect()->to(site_url('admin/usuarios'))->with('error', 'No se pudo eliminar el usuario.');
        }

        return redirect()->to(site_url('admin/usuarios'))->with('mensaje', 'Usuario eliminado correctamente.');
    }

    private function validarReglas(?int $id = null): bool
    {
        $usernameUnico = $id === null
            ? 'is_unique[users.username]'
            : 'is_unique[users.username,id,' . $id . ']';

        $reglas = [
            'username'         => 'required|min_length[3]|max_length[30]|regex_match[/\A[a-zA-Z0-9\.]+\z/]|' . $usernameUnico,
            'email'            => 'required|valid_email|max_length[254]',
            'nombre'           => 'required|min_length[2]|max_length[100]',
            'apellidos'        => 'required|min_length[2]|max_length[100]',
            'telefono'         => 'permit_empty|max_length[20]',
            'empresa_id'       => 'permit_empty|is_natural_no_zero',
            'password'         => $id === null ? 'required|min_length[8]|max_length[72]' : 'permit_empty|min_length[8]|max_length[72]',
            'password_confirm' => $id === null ? 'required|matches[password]' : 'permit_empty|matches[password]',
        ];

        return $this->validate($reglas);
    }

    private function datosFormulario(): array
    {
        return [
            'username'  => (string) $this->request->getPost('username'),
            'email'     => (string) $this->request->getPost('email'),
            'nombre'    => (string) $this->request->getPost('nombre'),
            'apellidos' => (string) $this->request->getPost('apellidos'),
            'telefono'  => (string) $this->request->getPost('telefono'),
            'password'  => (string) $this->request->getPost('password'),
            'active'    => (bool) $this->request->getPost('active'),
        ];
    }

    private function rolesFormulario(): array
    {
        $roles = $this->request->getPost('roles');

        if (! is_array($roles)) {
            return [];
        }

        return array_values(array_intersect($roles, array_keys(service('usuario')->rolesDisponibles())));
    }

    private function empresaFormulario(): ?int
    {
        $empresaId = $this->request->getPost('empresa_id');

        if ($empresaId === null || $empresaId === '') {
            return null;
        }

        return (int) $empresaId;
    }

    private function rolesRequierenEmpresa(array $roles): bool
    {
        return in_array('cliente', $roles, true) || in_array('empleado', $roles, true);
    }
}
