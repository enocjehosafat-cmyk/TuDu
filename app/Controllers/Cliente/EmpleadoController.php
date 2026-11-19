<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\EmpleadoModel;
use App\Models\EmpresaModel;
use App\Models\UsuariosShieldModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Shield\Entities\User;

class EmpleadoController extends BaseController
{
    public function index()
    {
        $data = (new EmpleadoModel())->listarPaginado($this->empresaId());

        return view('cliente/empleados/index', [
            'title'     => 'Empleados',
            'empleados' => $data['items'],
            'total'     => $data['total'],
            'page'      => $data['page'],
            'per'       => $data['per'],
        ]);
    }

    public function new()
    {
        return view('cliente/empleados/form', [
            'title'     => 'Nuevo empleado',
            'empleado'  => null,
            'usuario'   => null,
            'empresa'   => $this->empresa(),
            'esEdicion' => false,
        ]);
    }

    public function create()
    {
        if (! $this->validate($this->reglas())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
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
            service('usuario')->crear($datos, ['empleado'], $this->empresaId());
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al crear empleado: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'No se pudo crear el empleado. Revisa los datos e intenta de nuevo.');
        }

        return redirect()->to(site_url('cliente/empleados'))->with('mensaje', 'Empleado creado correctamente.');
    }

    public function edit($id)
    {
        $empleado = $this->buscarEmpleado($id);
        $usuario  = $this->usuarioDelEmpleado($empleado);

        $this->verificarPermiso($usuario);

        return view('cliente/empleados/form', [
            'title'     => 'Editar empleado',
            'empleado'  => $empleado,
            'usuario'   => $usuario,
            'empresa'   => $this->empresa(),
            'esEdicion' => true,
        ]);
    }

    public function update($id)
    {
        $empleado = $this->buscarEmpleado($id);
        $usuario  = $this->usuarioDelEmpleado($empleado);

        $this->verificarPermiso($usuario);

        if (! $this->validate($this->reglas((int) $usuario->id))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $datos = $this->datosFormulario();

        if ((int) $usuario->id === (int) auth()->id() && ! $datos['active']) {
            return redirect()->back()->withInput()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        if (! service('usuario')->emailDisponible($datos['email'], (int) $usuario->id)) {
            return redirect()->back()->withInput()->with('error', 'Ese correo ya está registrado.');
        }

        if (! service('usuario')->usernameDisponible($datos['username'], (int) $usuario->id)) {
            return redirect()->back()->withInput()->with('error', 'Ese nombre de usuario ya está registrado.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            service('usuario')->actualizar($usuario, $datos, null, $this->empresaId());
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al actualizar empleado: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'No se pudo actualizar el empleado. Revisa los datos e intenta de nuevo.');
        }

        return redirect()->to(site_url('cliente/empleados'))->with('mensaje', 'Empleado actualizado correctamente.');
    }

    public function delete($id)
    {
        $empleado = $this->buscarEmpleado($id);
        $usuario  = $this->usuarioDelEmpleado($empleado);

        $this->verificarPermiso($usuario);

        if ((int) $usuario->id === (int) auth()->id()) {
            return redirect()->to(site_url('cliente/empleados'))->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            service('usuario')->eliminar($usuario);
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al eliminar empleado: ' . $e->getMessage());

            return redirect()->to(site_url('cliente/empleados'))->with('error', 'No se pudo eliminar el empleado.');
        }

        return redirect()->to(site_url('cliente/empleados'))->with('mensaje', 'Empleado eliminado correctamente.');
    }

    private function empresa(): array
    {
        $empresa = (new EmpresaModel())->find($this->empresaId());

        if ($empresa === null) {
            throw PageNotFoundException::forPageNotFound('Empresa no encontrada.');
        }

        return $empresa;
    }

    private function buscarEmpleado($id): array
    {
        $empleado = (new EmpleadoModel())
            ->where('id', (int) $id)
            ->where('empresa_id', $this->empresaId())
            ->first();

        if ($empleado === null) {
            throw PageNotFoundException::forPageNotFound('Empleado no encontrado.');
        }

        return $empleado;
    }

    private function usuarioDelEmpleado(array $empleado): User
    {
        $usuario = (new UsuariosShieldModel())->find((int) $empleado['usuario_id']);

        if ($usuario === null) {
            throw PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        return $usuario;
    }

    private function verificarPermiso(User $usuario): void
    {
        $roles          = service('usuario')->rolesDe($usuario);
        $esSuperadmin   = in_array('superadmin', $roles, true);
        $esCliente      = in_array('cliente', $roles, true);
        $esEmpleado     = in_array('empleado', $roles, true);
        $esMiMismo      = (int) $usuario->id === (int) auth()->id();
        $actorEsCliente = $this->esCliente();

        if ($esSuperadmin) {
            throw PageNotFoundException::forPageNotFound('Empleado no encontrado.');
        }

        if ($esCliente && ! $esMiMismo) {
            throw PageNotFoundException::forPageNotFound('No puedes modificar a otro cliente.');
        }

        if (! $actorEsCliente) {
            if (! $esMiMismo || ! $esEmpleado) {
                throw PageNotFoundException::forPageNotFound('No tienes permiso para gestionar este empleado.');
            }
        }
    }

    private function reglas(?int $id = null): array
    {
        $usernameUnico = $id === null
            ? 'is_unique[users.username]'
            : 'is_unique[users.username,id,' . $id . ']';

        return [
            'nombre'           => 'required|min_length[2]|max_length[100]',
            'apellidos'        => 'required|min_length[2]|max_length[100]',
            'username'         => 'required|min_length[3]|max_length[30]|regex_match[/\A[a-zA-Z0-9\.]+\z/]|' . $usernameUnico,
            'email'            => 'required|valid_email|max_length[254]',
            'telefono'         => 'permit_empty|max_length[20]',
            'password'         => $id === null ? 'required|min_length[8]|max_length[72]' : 'permit_empty|min_length[8]|max_length[72]',
            'password_confirm' => $id === null ? 'required|matches[password]' : 'permit_empty|matches[password]',
        ];
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
}