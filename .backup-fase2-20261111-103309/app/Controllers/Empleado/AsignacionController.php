<?php

namespace App\Controllers\Empleado;

use App\Controllers\BaseController;
use App\Models\AsignacionTareaModel;
use App\Models\ChecklistTareaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class AsignacionController extends BaseController
{
    public function index()
    {
        $perfil = $this->perfilActual();

        $asignaciones = [];

        if ($perfil !== null) {
            $asignaciones = (new AsignacionTareaModel())
                ->listarDeEmpleado((int) $perfil['id']);
        }

        return view('empleado/asignaciones/index', [
            'title'        => 'Mis asignaciones',
            'asignaciones' => $asignaciones,
        ]);
    }

public function show($id)
{
    $asignacion = $this->buscarAsignacion($id);
    $checklist  = (new ChecklistTareaModel())->listarPorAsignacion((int) $asignacion['id']);

    return view('empleado/asignaciones/show', [
        'title'       => 'Detalle de asignación',
        'asignacion'  => $asignacion,
        'checklist'   => $checklist,
        'soloLectura' => $asignacion['estatus'] === 'cancelada',
    ]);
}

    public function guardarChecklist($id)
    {
        $asignacion = $this->buscarAsignacion($id);
        
        if ($asignacion['estatus'] === 'cancelada') {
            return redirect()->to(site_url('empleado/asignaciones'))->with('error', 'Esta asignación ha sido cancelada.');
        }

        $model        = new ChecklistTareaModel();
        $marcados     = array_map('intval', (array) ($this->request->getPost('completada') ?? []));
        $actualizados = 0;

        $db = db_connect();
        $db->transBegin();

        try {
            foreach ($model->listarPorAsignacion((int) $asignacion['id']) as $item) {
                $completada = in_array((int) $item['id'], $marcados, true) ? 1 : 0;

                if ((int) $item['completada'] === $completada) {
                    continue;
                }

                if (! $model->update((int) $item['id'], $completada === 1 ? ['completada' => 1, 'fecha_completada' => date('Y-m-d H:i:s')] : ['completada' => 0, 'fecha_completada' => null])) {
                    throw new \RuntimeException('No se pudo actualizar el item ' . $item['id']);
                }

                $actualizados++;
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al guardar checklist: ' . $e->getMessage());

            return redirect()->to(site_url('empleado/asignaciones/' . $asignacion['id']))->with('error', 'No se pudo guardar el checklist.');
        }

        return redirect()->to(site_url('empleado/asignaciones/' . $asignacion['id']))->with('mensaje', $actualizados > 0 ? 'Checklist actualizado correctamente.' : 'Sin cambios en el checklist.');
    }

    public function completar($id)
    {
        $asignacion = $this->buscarAsignacion($id);

        if ($asignacion['estatus'] === 'completada') {
            return redirect()->to(site_url('empleado/asignaciones/' . $asignacion['id']))->with('error', 'Esta asignación ya está completada.');
        }
        
        if ($asignacion['estatus'] === 'cancelada') {
            return redirect()->to(site_url('empleado/asignaciones/' . $asignacion['id']))->with('error', 'Esta asignación ha sido cancelada y no se puede completar.');
        }

        $model = new AsignacionTareaModel();

        if (! $model->update((int) $asignacion['id'], ['estatus' => 'completada'])) {
            log_message('error', 'Error al completar asignación: ' . implode('; ', $model->errors()));

            return redirect()->to(site_url('empleado/asignaciones/' . $asignacion['id']))->with('error', 'No se pudo completar la asignación.');
        }

        return redirect()->to(site_url('empleado/asignaciones/' . $asignacion['id']))->with('mensaje', 'Asignación completada correctamente.');
    }

    private function buscarAsignacion($id): array
    {
        $perfil = $this->perfilActual();

        if ($perfil === null) {
            throw PageNotFoundException::forPageNotFound('No tienes un perfil de empleado asociado.');
        }

        $asignacion = (new AsignacionTareaModel())
            ->buscarDeEmpleado((int) $id, (int) $perfil['id']);

        if ($asignacion === null) {
            throw PageNotFoundException::forPageNotFound('Asignación no encontrada.');
        }

        return $asignacion;
    }
}