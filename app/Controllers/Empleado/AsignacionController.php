<?php

namespace App\Controllers\Empleado;

use App\Controllers\BaseController;
use App\Models\AsignacionTareaModel;
use App\Models\ChecklistTareaModel;
use App\Models\EvidenciaAsignacionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class AsignacionController extends BaseController
{
    public function index()
    {
        $perfil = $this->perfilActual();
        $asignaciones = [];

        if ($perfil !== null) {
            $asignaciones = (new AsignacionTareaModel())->listarDeEmpleado((int) $perfil['id']);
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
        $evidencias = (new EvidenciaAsignacionModel())->listarPorAsignacion((int) $asignacion['id']);

        return view('empleado/asignaciones/show', [
            'title'       => 'Detalle de asignación',
            'asignacion'  => $asignacion,
            'checklist'   => $checklist,
            'evidencias'  => $evidencias,
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

    // NUEVO MÉTODO: Guardar Evidencia General (Hasta 5 fotos)
        public function guardarEvidenciaGeneral($id)
    {
        // Forzar lectura de POST para evitar problemas con CSRF
        $this->request->setGlobal('post', $this->request->getPost());

        $asignacion = $this->buscarAsignacion($id);

        if ($asignacion['estatus'] === 'cancelada' || $asignacion['estatus'] === 'completada') {
            return $this->response->setJSON(['success' => false, 'message' => 'La asignación está cerrada.']);
        }
        // (Validación de móvil eliminada para permitir subir fotos desde PC)
$model = new EvidenciaAsignacionModel();
        
        // Verificar límite de 5 fotos
        $fotosActuales = $model->where('asignacion_id', $asignacion['id'])->countAllResults();
        if ($fotosActuales >= 5) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya has subido el límite de 5 fotos.']);
        }

        $file = $this->request->getFile('foto');
        
        // Validar que el archivo exista y no tenga errores de subida
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se recibió la foto o hubo un error en la subida.']);
        }

        // Validar que sea una imagen real (evita que suban archivos falsos)
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp', 'image/heic'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'El archivo no es una imagen válida.']);
        }

        // Guardar archivo físico
        if (!is_dir(FCPATH . 'uploads/evidencias')) {
            mkdir(FCPATH . 'uploads/evidencias', 0777, true);
        }
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/evidencias', $newName);

        // Guardar en Base de Datos
        $data = [
            'asignacion_id' => $asignacion['id'],
            'foto'          => $newName,
            'latitud'       => $this->request->getPost('latitud') ?: null,
            'longitud'      => $this->request->getPost('longitud') ?: null,
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Evidencia guardada.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
    }
        public function eliminarEvidencia($asignacionId, $evidenciaId)
    {
        $asignacion = $this->buscarAsignacion($asignacionId);

        if ($asignacion['estatus'] === 'cancelada' || $asignacion['estatus'] === 'completada' || $asignacion['estatus'] === 'en_revision') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'No puedes modificar evidencias en este estado.']);
            }
            return redirect()->to(site_url('empleado/asignaciones/' . $asignacionId))->with('error', 'No puedes modificar evidencias en este estado.');
        }

        $model = new EvidenciaAsignacionModel();
        $evidencia = $model->find($evidenciaId);

        if (!$evidencia || (int)$evidencia['asignacion_id'] !== (int)$asignacionId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Evidencia no encontrada.']);
            }
            return redirect()->to(site_url('empleado/asignaciones/' . $asignacionId))->with('error', 'Evidencia no encontrada.');
        }

        $rutaArchivo = FCPATH . 'uploads/evidencias/' . $evidencia['foto'];
        if (file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }

        $model->delete($evidenciaId);

        if (! $this->request->isAJAX()) {
            return redirect()->to(site_url('empleado/asignaciones/' . $asignacionId))->with('success', 'Foto eliminada correctamente.');
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Foto eliminada correctamente.']);
    }


    public function completar($id)
    {
        $asignacion = $this->buscarAsignacion($id);

        if ($asignacion['estatus'] === 'completada' || $asignacion['estatus'] === 'en_revision') {
            return redirect()->to(site_url('empleado/asignaciones/' . $asignacion['id']))->with('error', 'Esta asignación ya está en revisión o completada.');
        }
        
        if ($asignacion['estatus'] === 'cancelada') {
            return redirect()->to(site_url('empleado/asignaciones/' . $asignacion['id']))->with('error', 'Esta asignación ha sido cancelada.');
        }

        // NUEVA VALIDACIÓN: Verificar que tenga exactamente 5 fotos
        $totalFotos = (new \App\Models\EvidenciaAsignacionModel())->where('asignacion_id', $asignacion['id'])->countAllResults();
        
        if ($totalFotos < 5) {
            return redirect()->to(site_url('empleado/asignaciones/' . $asignacion['id']))->with('error', 'No puedes enviar a revisión sin antes subir las 5 fotos obligatorias. Llevas ' . $totalFotos . ' de 5.');
        }

        $model = new AsignacionTareaModel();
        $model->update((int) $asignacion['id'], [
            'estatus'          => 'en_revision',
            'fecha_completada' => date('Y-m-d H:i:s'), // Guardamos la hora en que terminó
        ]);

        $tareaNombre = $asignacion['tarea'] ?? 'Tarea eliminada';
        service('audit')->log(
            'enviar_revision', 
            'asignacion', 
            (int) $asignacion['id'], 
            "Envió a revisión la tarea '{$tareaNombre}'"
        );

        return redirect()->to(site_url('empleado/asignaciones/' . $asignacion['id']))->with('success', 'Tarea enviada a revisión. Espera la aprobación de tu jefe.');
    }    private function buscarAsignacion($id): array
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

