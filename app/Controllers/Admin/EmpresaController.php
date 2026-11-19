<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmpresaModel;

class EmpresaController extends BaseController
{
    public function index()
    {
        return view('admin/empresas/index', [
            'title'     => 'Gestión de empresas',
            'empresas'  => (new EmpresaModel())->orderBy('nombre', 'ASC')->findAll(),
        ]);
    }

    public function new()
    {
        return view('admin/empresas/form', [
            'title'   => 'Nueva empresa',
            'empresa' => null,
        ]);
    }

    public function create()
    {
        if (! $this->validarEmpresa()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new EmpresaModel())->insert([
            'nombre'    => $this->request->getPost('nombre'),
            'rfc'       => $this->request->getPost('rfc'),
            'direccion' => $this->request->getPost('direccion'),
            'estatus'   => 1,
        ]);

        return redirect()->to(site_url('admin/empresas'))->with('mensaje', 'Empresa creada correctamente.');
    }

    public function edit($id)
    {
        $empresa = (new EmpresaModel())->find($id);

        if ($empresa === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Empresa no encontrada.');
        }

        return view('admin/empresas/form', [
            'title'   => 'Editar empresa',
            'empresa' => $empresa,
        ]);
    }

    public function update($id)
    {
        $empresaModel = new EmpresaModel();

        if ($empresaModel->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Empresa no encontrada.');
        }

        if (! $this->validarEmpresa()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $empresaModel->update($id, [
            'nombre'    => $this->request->getPost('nombre'),
            'rfc'       => $this->request->getPost('rfc'),
            'direccion' => $this->request->getPost('direccion'),
        ]);

        return redirect()->to(site_url('admin/empresas'))->with('mensaje', 'Empresa actualizada correctamente.');
    }

    public function delete($id)
    {
        $empresaModel = new EmpresaModel();

        if ($empresaModel->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Empresa no encontrada.');
        }

        $empresaModel->update($id, ['estatus' => 0]);

        return redirect()->to(site_url('admin/empresas'))->with('mensaje', 'Empresa desactivada.');
    }

    public function activar($id)
    {
        $empresaModel = new EmpresaModel();

        if ($empresaModel->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Empresa no encontrada.');
        }

        $empresaModel->update($id, ['estatus' => 1]);

        return redirect()->to(site_url('admin/empresas'))->with('mensaje', 'Empresa activada.');
    }

    private function validarEmpresa(): bool
    {
        return $this->validate([
            'nombre'    => 'required|min_length[2]|max_length[150]',
            'rfc'       => 'permit_empty|max_length[15]',
            'direccion' => 'permit_empty|max_length[255]',
        ]);
    }
    public function eliminar($id)
    {
        $empresaModel = new EmpresaModel();
        $empresa = $empresaModel->find($id);

        if ($empresa === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Empresa no encontrada.');
        }

        // Validar que no tenga usuarios asignados
        $usuarios = db_connect()->table('empleados')->where('empresa_id', $id)->countAllResults();
        if ($usuarios > 0) {
            return redirect()->to(site_url('admin/empresas'))
                ->with('error', 'No puedes eliminar esta empresa porque tiene empleados asignados. Desactívala primero o elimina a sus empleados.');
        }

        $empresaModel->delete($id);
        service('audit')->log(
            'eliminar', 
            'empresa', 
            (int) $id, 
            "Eliminó permanentemente la empresa '{$empresa['nombre']}'"
        );
        return redirect()->to(site_url('admin/empresas'))
            ->with('success', 'Empresa eliminada permanentemente del sistema.');
    }
}