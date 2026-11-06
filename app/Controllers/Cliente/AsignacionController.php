<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\AsignacionTareaModel;
use App\Models\ChecklistTareaModel;
use App\Models\EmpleadoModel;
use App\Models\TareaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class AsignacionController extends BaseController
{
    public function index()
    {
        return view('cliente/asignaciones/index', [
            'title'        => 'Asignaciones',
            'asignaciones' => (new AsignacionTareaModel())->listarPorEmpresa($this->empresaId()),
        ]);
    }

    public function new()
    {
        return view('cliente/asignaciones/form', [
            'title'       => 'Nueva asignación',
            'asignacion'  => null,
            'tareas'      => (new TareaModel())->listarActivasPorEmpresa($this->empresaId()),
            'empleados'   => (new EmpleadoModel())->where('empresa_id', $this->empresaId())->where('estatus', 1)->orderBy('nombre', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        if (! $this->validate($this->reglas())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tareaId    = (int) $this->request->getPost('tarea_id');
        $empleadoId = (int) $this->request->getPost('empleado_id');

        if (! $this->tareaPerteneceAEmpresa($tareaId) || ! $this->empleadoPerteneceAEmpresa($empleadoId)) {
            return redirect()->back()->withInput()->with('error', 'La tarea o el empleado no pertenecen a tu empresa.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $asignacionId = (new AsignacionTareaModel())->insert($this->datosFormulario(), true);
            if ($asignacionId === false) {
                throw new \RuntimeException('No se pudo insertar la asignación.');
            }

            $actividades = (new TareaModel())->getActividades($tareaId);
            (new ChecklistTareaModel())->crearItems($asignacionId, $actividades);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al crear asignación: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'No se pudo crear la asignación.');
        }

        return redirect()->to(site_url('cliente/asignaciones'))->with('mensaje', 'Asignación creada correctamente.');
    }

    public function edit($id)
    {
        $asignacion = $this->buscarAsignacion($id);
        return view('cliente/asignaciones/form', [
            'title'       => 'Editar asignación',
            'asignacion'  => $asignacion,
            'tareas'      => (new TareaModel())->listarActivasPorEmpresa($this->empresaId()),
            'empleados'   => (new EmpleadoModel())->where('empresa_id', $this->empresaId())->where('estatus', 1)->orderBy('nombre', 'ASC')->findAll(),
        ]);
    }

    public function update($id)
    {
        $asignacion = $this->buscarAsignacion($id);
        if (! $this->validate($this->reglas())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tareaId    = (int) $this->request->getPost('tarea_id');
        $empleadoId = (int) $this->request->getPost('empleado_id');

        if (! $this->tareaPerteneceAEmpresa($tareaId) || ! $this->empleadoPerteneceAEmpresa($empleadoId)) {
            return redirect()->back()->withInput()->with('error', 'La tarea o el empleado no pertenecen a tu empresa.');
        }

        (new AsignacionTareaModel())->update((int) $asignacion['id'], $this->datosFormulario());
        return redirect()->to(site_url('cliente/asignaciones'))->with('mensaje', 'Asignación actualizada correctamente.');
    }

    public function delete($id)
    {
        $asignacion = $this->buscarAsignacion($id);
        (new AsignacionTareaModel())->delete((int) $asignacion['id']);
        return redirect()->to(site_url('cliente/asignaciones'))->with('mensaje', 'Asignación eliminada correctamente.');
    }

    private function buscarAsignacion($id): array
    {
        $asignacion = (new AsignacionTareaModel())->buscarDeEmpresa((int) $id, $this->empresaId());
        if ($asignacion === null) {
            throw PageNotFoundException::forPageNotFound('Asignación no encontrada.');
        }
        return $asignacion;
    }

    private function tareaPerteneceAEmpresa(int $tareaId): bool
    {
        return (new TareaModel())->where('id', $tareaId)->where('empresa_id', $this->empresaId())->countAllResults() > 0;
    }

    private function empleadoPerteneceAEmpresa(int $empleadoId): bool
    {
        return (new EmpleadoModel())->where('id', $empleadoId)->where('empresa_id', $this->empresaId())->countAllResults() > 0;
    }

    private function reglas(): array
    {
        return [
            'tarea_id'          => 'required|is_natural_no_zero',
            'empleado_id'       => 'required|is_natural_no_zero',
            'fecha'             => 'required|valid_date[Y-m-d]',
            'fecha_vencimiento' => 'permit_empty|valid_date[Y-m-d]',
            'prioridad'         => 'required|in_list[baja,media,alta]',
            'notas'             => 'permit_empty|max_length[2000]',
        ];
    }

    private function datosFormulario(): array
    {
        return [
            'tarea_id'          => (int) $this->request->getPost('tarea_id'),
            'empleado_id'       => (int) $this->request->getPost('empleado_id'),
            'fecha'             => (string) $this->request->getPost('fecha'),
            'fecha_vencimiento' => $this->request->getPost('fecha_vencimiento') ?: null,
            'prioridad'         => (string) $this->request->getPost('prioridad'),
            'notas'             => (string) $this->request->getPost('notas'),
        ];
    }
}
