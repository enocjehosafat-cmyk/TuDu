<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\TareaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class TareaController extends BaseController
{
    public function index()
    {
        $data = (new TareaModel())->listarPaginado($this->empresaId());

        return view('cliente/tareas/index', [
            'title'  => 'Tareas',
            'tareas' => $data['items'],
            'pager'  => $data['pager'],
        ]);
    }

    public function new()
    {
        return view('cliente/tareas/form', ['title' => 'Nueva tarea', 'tarea' => null]);
    }

    public function create()
    {
        if (! $this->validate($this->reglas())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new TareaModel();
        $datos = $this->datosFormulario();
        $datos['empresa_id'] = $this->empresaId();

        $actividades = (array) ($this->request->getPost('actividades') ?? []);

        if (! $model->guardarTarea($datos, $actividades)) {
            return redirect()->back()->withInput()->with('error', 'No se pudo crear la tarea.');
        }

        return redirect()->to(site_url('cliente/tareas'))->with('mensaje', 'Tarea creada correctamente.');
    }

    public function edit($id)
    {
        return view('cliente/tareas/form', ['title' => 'Editar tarea', 'tarea' => $this->buscarTarea($id)]);
    }

    public function update($id)
    {
        $tarea = $this->buscarTarea($id);

        if (! $this->validate($this->reglas())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new TareaModel();
        $datos = $this->datosFormulario();
        $datos['empresa_id'] = $this->empresaId();
        $datos['id']         = (int) $tarea['id'];

        $actividades = (array) ($this->request->getPost('actividades') ?? []);

        if (! $model->guardarTarea($datos, $actividades)) {
            return redirect()->back()->withInput()->with('error', 'No se pudo actualizar la tarea.');
        }

        return redirect()->to(site_url('cliente/tareas'))->with('mensaje', 'Tarea actualizada correctamente.');
    }

public function delete($id)
{
    $tarea = $this->buscarTarea($id);
    $model = new TareaModel();

    $db = db_connect();
    $db->transBegin();

    try {
        // Soft-delete de la tarea
        $model->update((int) $tarea['id'], ['estatus' => 0]);

        // Solo cancelar las asignaciones pendientes.
        // Las completadas conservan su estatus para el historial.
        db_connect()->table('asignaciones_tareas')
            ->where('tarea_id', $tarea['id'])
            ->where('estatus', 'pendiente')
            ->update(['estatus' => 'cancelada']);

        $db->transCommit();
    } catch (\Throwable $e) {
        $db->transRollback();
        log_message('error', 'Error al desactivar tarea: ' . $e->getMessage());

        return redirect()->back()->with('error', 'No se pudo desactivar la tarea.');
    }

    return redirect()->to(site_url('cliente/tareas'))
        ->with('mensaje', 'Tarea desactivada. Las asignaciones completadas se conservan en el historial.');
}

    private function buscarTarea($id): array
    {
        $tarea = (new TareaModel())->where('id', (int) $id)->where('empresa_id', $this->empresaId())->first();
        if ($tarea === null) {
            throw PageNotFoundException::forPageNotFound('Tarea no encontrada.');
        }

        return $tarea;
    }

    private function reglas(): array
    {
        return [
            'nombre'          => 'required|min_length[2]|max_length[100]',
            'descripcion'     => 'permit_empty|max_length[2000]',
            'tiempo_estimado' => 'permit_empty|is_natural',
            'fecha_inicio'    => 'permit_empty|valid_date[Y-m-d]',
            'fecha_fin'       => 'permit_empty|valid_date[Y-m-d]',
        ];
    }

    private function datosFormulario(): array
    {
        return [
            'empresa_id'      => $this->empresaId(),
            'nombre'          => (string) $this->request->getPost('nombre'),
            'descripcion'     => (string) $this->request->getPost('descripcion'),
            'tiempo_estimado' => $this->request->getPost('tiempo_estimado') !== '' ? (int) $this->request->getPost('tiempo_estimado') : null,
            'es_diaria'       => $this->request->getPost('es_diaria') ? 1 : 0,
            'fecha_inicio'    => $this->request->getPost('fecha_inicio') ?: null,
            'fecha_fin'       => $this->request->getPost('fecha_fin') ?: null,
        ];
    }
}