<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\AsignacionTareaModel;
use App\Models\ChecklistTareaModel;
use App\Models\EmpleadoModel;
use App\Models\TareaModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\EvidenciaAsignacionModel;

class AsignacionController extends BaseController
{
    public function index()
    {
        $data = (new AsignacionTareaModel())->listarPaginado($this->empresaId());

        return view('cliente/asignaciones/index', [
            'title'        => 'Asignaciones',
            'asignaciones' => $data['items'],
            'total'        => $data['total'],
            'page'         => $data['page'],
            'per'          => $data['per'],
        ]);
    }

    public function historial()
    {
        $data = (new AsignacionTareaModel())->listarHistorialPaginado($this->empresaId());

        return view('cliente/asignaciones/historial', [
            'title'        => 'Historial de asignaciones',
            'asignaciones' => $data['items'],
            'total'        => $data['total'],
            'page'         => $data['page'],
            'per'          => $data['per'],
        ]);
    }

    public function show($id)
    {
        $asignacion = $this->buscarAsignacion($id);
        $empleado = (new EmpleadoModel())->find((int) $asignacion['empleado_id']);
        $asignacion['empleado'] = $empleado ? trim($empleado['nombre'] . ' ' . $empleado['apellidos']) : '—';
        
        // NUEVO: Buscar las evidencias
        $evidencias = (new \App\Models\EvidenciaAsignacionModel())->listarPorAsignacion((int) $asignacion['id']);

        return view('cliente/asignaciones/show', [
            'title'      => 'Detalle de asignación',
            'asignacion' => $asignacion,
            'evidencias' => $evidencias, // <-- Lo pasamos a la vista
        ]);
    }

    public function new()
    {
        return view('cliente/asignaciones/form', [
            'title'       => 'Nueva asignación',
            'asignacion'  => null,
            'tareas'      => (new TareaModel())->listarActivasPorEmpresa($this->empresaId()),
            'empleados'   => $this->obtenerEmpleadosParaAsignar(),
        ]);
    }

    public function create()
    {
        if (! $this->validate($this->reglas())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $fecha = $this->request->getPost('fecha');
        $fechaVencimiento = $this->request->getPost('fecha_vencimiento');
        if ($fechaVencimiento && $fechaVencimiento < $fecha) {
            return redirect()->back()->withInput()->with('error', 'La fecha de vencimiento no puede ser anterior a la fecha de la asignación.');
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
            (new ChecklistTareaModel())->crearItems((int) $asignacionId, $actividades);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al crear asignación: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'No se pudo crear la asignación.');
        }

        return redirect()->to(site_url('cliente/asignaciones'))->with('success', 'Asignación creada correctamente.');
    }

    public function edit($id)
    {
        $asignacion = $this->buscarAsignacion($id);

        if (($asignacion['estatus'] ?? '') === 'cancelada' || ($asignacion['estatus'] ?? '') === 'completada') {
            return redirect()->to(site_url('cliente/asignaciones/show/' . $asignacion['id']));
        }

        // Cargamos el checklist actual
        $checklist = (new ChecklistTareaModel())->listarPorAsignacion((int) $asignacion['id']);

        return view('cliente/asignaciones/form', [
            'title'       => 'Editar asignación',
            'asignacion'  => $asignacion,
            'tareas'      => (new TareaModel())->listarActivasPorEmpresa($this->empresaId()),
            'empleados'   => $this->obtenerEmpleadosParaAsignar(),
            'checklist'   => $checklist, // <-- Pasamos el checklist a la vista
        ]);
    }

    public function update($id)
    {
        $asignacion = $this->buscarAsignacion($id);

        if (($asignacion['estatus'] ?? '') === 'cancelada' || ($asignacion['estatus'] ?? '') === 'completada') {
            return redirect()->to(site_url('cliente/asignaciones/show/' . $asignacion['id']));
        }

        if (! $this->validate($this->reglas())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $fecha = $this->request->getPost('fecha');
        $fechaVencimiento = $this->request->getPost('fecha_vencimiento');
        if ($fechaVencimiento && $fechaVencimiento < $fecha) {
            return redirect()->back()->withInput()->with('error', 'La fecha de vencimiento no puede ser anterior a la fecha de la asignación.');
        }

        $tareaId    = (int) $this->request->getPost('tarea_id');
        $empleadoId = (int) $this->request->getPost('empleado_id');

        if (! $this->tareaPerteneceAEmpresa($tareaId) || ! $this->empleadoPerteneceAEmpresa($empleadoId)) {
            return redirect()->back()->withInput()->with('error', 'La tarea o el empleado no pertenecen a tu empresa.');
        }

        $tareaAnteriorId = (int) $asignacion['tarea_id'];
        $nuevaTareaId    = (int) $this->request->getPost('tarea_id');

        $db = db_connect();
        $db->transBegin();

        try {
            (new AsignacionTareaModel())->update((int) $asignacion['id'], $this->datosFormulario());

            if ($tareaAnteriorId !== $nuevaTareaId) {
                (new ChecklistTareaModel())->where('asignacion_id', $asignacion['id'])->delete();
                $actividades = (new TareaModel())->getActividades($nuevaTareaId);
                (new ChecklistTareaModel())->crearItems((int) $asignacion['id'], $actividades);
            }

            // Guardar los ítems del checklist manual
            $checklistItems = $this->request->getPost('checklist') ?? [];
            $checklistIds   = $this->request->getPost('checklist_id') ?? [];
            
            $checklistModel = new ChecklistTareaModel();
            
            foreach ($checklistItems as $index => $nota) {
                $nota = trim($nota);
                if ($nota === '') continue;
                
                $idExistente = $checklistIds[$index] ?? 'nuevo';
                
                if ($idExistente === 'nuevo') {
                    $checklistModel->insert([
                        'asignacion_id' => $asignacion['id'],
                        'notas'         => $nota,
                        'completada'    => 0,
                    ]);
                } else {
                    $checklistModel->update((int) $idExistente, [
                        'notas' => $nota,
                    ]);
                }
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al actualizar asignación: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'No se pudo actualizar la asignación.');
        }
        return redirect()->to(site_url('cliente/asignaciones'))->with('success', 'Asignación actualizada correctamente.');
    }
    public function delete($id)
    {
        $asignacion = $this->buscarAsignacion($id);
        $estatus    = $asignacion['estatus'] ?? 'pendiente';

        if ($estatus === 'completada') {
            return redirect()->to(site_url('cliente/asignaciones'))
                ->with('error', 'No puedes cancelar una asignación ya completada.');
        }

        if ($estatus === 'cancelada') {
            return redirect()->to(site_url('cliente/asignaciones'))
                ->with('error', 'Esta asignación ya está cancelada.');
        }

        (new AsignacionTareaModel())->update((int) $asignacion['id'], ['estatus' => 'cancelada']);
        
        $tareaNombre = $asignacion['tarea'] ?? 'Tarea eliminada';
        service('audit')->log(
            'cancelar', 
            'asignacion', 
            (int) $asignacion['id'], 
            "Canceló la asignación de '{$tareaNombre}'"
        );
        return redirect()->to(site_url('cliente/asignaciones'))
            ->with('warning', 'Asignación cancelada. El historial y el checklist se conservan.');
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
        private function obtenerEmpleadosParaAsignar()
    {
        $empleados = (new EmpleadoModel())
            ->where('empresa_id', $this->empresaId())
            ->where('estatus', 1)
            ->orderBy('nombre', 'ASC')
            ->findAll();

        // Filtramos para quitar al usuario actual (el cliente logueado)
        $usuarioActualId = auth()->id();
        
        return array_filter($empleados, static function ($empleado) use ($usuarioActualId) {
            return (int) $empleado['usuario_id'] !== (int) $usuarioActualId;
        });
    }
    public function aprobar($id)
    {
        $asignacion = $this->buscarAsignacion($id);

        if ($asignacion['estatus'] !== 'en_revision') {
            return redirect()->back()->with('error', 'Esta tarea no está en revisión.');
        }

        (new AsignacionTareaModel())->update((int) $asignacion['id'], [
            'estatus' => 'completada',
        ]);

        service('audit')->log(
            'aprobar', 
            'asignacion', 
            (int) $asignacion['id'], 
            "Aprobó la asignación de '{$asignacion['tarea']}'"
        );

        return redirect()->to(site_url('cliente/asignaciones'))->with('success', 'Tarea aprobada y completada exitosamente.');
    }

    public function rechazar($id)
    {
        $asignacion = $this->buscarAsignacion($id);

        if ($asignacion['estatus'] !== 'en_revision') {
            return redirect()->back()->with('error', 'Esta tarea no está en revisión.');
        }

        (new AsignacionTareaModel())->update((int) $asignacion['id'], [
            'estatus' => 'pendiente',
            'fecha_completada' => null, // Borramos la fecha para que la vuelva a hacer
        ]);

        service('audit')->log(
            'rechazar', 
            'asignacion', 
            (int) $asignacion['id'], 
            "Rechazó la asignación de '{$asignacion['tarea']}'"
        );

        return redirect()->to(site_url('cliente/asignaciones'))->with('warning', 'Tarea rechazada. El empleado deberá realizarla de nuevo.');
    }
}