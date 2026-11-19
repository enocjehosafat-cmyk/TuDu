<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class GenerarTareasDiarias extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'tareas:generar';
    protected $description = 'Genera las asignaciones del día siguiente para las tareas diarias (es_diaria = 1).';
    protected $usage       = 'tareas:generar [fecha_destino]';
    protected $arguments   = [
        'fecha_destino' => 'Fecha a generar en formato Y-m-d. Por defecto es mañana.',
    ];

    public function run(array $params)
    {
        $db = db_connect();

        // Fecha destino (por defecto mañana)
        $fechaDestino = $params[0] ?? date('Y-m-d', strtotime('+1 day'));

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDestino)) {
            CLI::error("La fecha '{$fechaDestino}' no tiene el formato correcto (Y-m-d).");
            return EXIT_ERROR;
        }

        $fechaOrigen = date('Y-m-d', strtotime('-1 day', strtotime($fechaDestino)));

        CLI::write("Generando asignaciones para: {$fechaDestino}", 'yellow');
        CLI::write("Basado en las asignaciones del día: {$fechaOrigen}", 'yellow');
        CLI::newLine();

        // 1. Buscar todas las asignaciones de HOY (fechaOrigen) cuya tarea sea diaria
        $asignacionesBase = $db->table('asignaciones_tareas a')
            ->select('a.tarea_id, a.empleado_id, a.prioridad, a.notas, t.nombre as tarea_nombre')
            ->join('tareas t', 't.id = a.tarea_id', 'inner')
            ->where('a.fecha', $fechaOrigen)
            ->where('t.es_diaria', 1)
            ->where('t.estatus', 1) // La tarea debe estar activa
            ->where('a.estatus !=', 'cancelada')
            ->groupStart()
                ->where('t.fecha_fin', null)
                ->orWhere('t.fecha_fin >=', $fechaDestino)
            ->groupEnd()
            ->groupStart()
                ->where('t.fecha_inicio', null)
                ->orWhere('t.fecha_inicio <=', $fechaDestino)
            ->groupEnd()
            ->get()
            ->getResultArray();

        if ($asignacionesBase === []) {
            CLI::write('No hay tareas diarias asignadas hoy. Nada que generar.', 'yellow');
            return EXIT_SUCCESS;
        }

        $creadas   = 0;
        $omitidas  = 0;
        $errores   = 0;

        $db->transBegin();

        try {
            foreach ($asignacionesBase as $base) {
                // 2. Evitar duplicados: ¿ya existe esta asignación para la fecha destino?
                $existe = $db->table('asignaciones_tareas')
                    ->where('tarea_id', $base['tarea_id'])
                    ->where('empleado_id', $base['empleado_id'])
                    ->where('fecha', $fechaDestino)
                    ->countAllResults();

                if ($existe > 0) {
                    $omitidas++;
                    continue;
                }

                // 3. Crear la asignación para mañana
                $db->table('asignaciones_tareas')->insert([
                    'tarea_id'          => $base['tarea_id'],
                    'empleado_id'       => $base['empleado_id'],
                    'fecha'             => $fechaDestino,
                    'fecha_vencimiento' => null,
                    'prioridad'         => $base['prioridad'],
                    'notas'             => $base['notas'],
                    'estatus'           => 'pendiente',
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ]);

                $nuevoId = (int) $db->insertID();

                // 4. Copiar el checklist de la tarea a la nueva asignación
                $tarea = $db->table('tareas')->where('id', $base['tarea_id'])->get()->getRowArray();

                if (! empty($tarea['actividades'])) {
                    $actividades = json_decode($tarea['actividades'], true) ?? [];
                    $items       = [];

                    foreach ($actividades as $act) {
                        $act = trim((string) $act);

                        if ($act === '') {
                            continue;
                        }

                        $items[] = [
                            'asignacion_id' => $nuevoId,
                            'completada'    => 0,
                            'notas'         => mb_substr($act, 0, 2000),
                            'created_at'    => date('Y-m-d H:i:s'),
                        ];
                    }

                    if ($items !== []) {
                        $db->table('checklist_tareas')->insertBatch($items);
                    }
                }

                $creadas++;
                CLI::write("  ✓ Asignación #{$nuevoId} creada para tarea '{$base['tarea_nombre']}'", 'green');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            CLI::error('Error al generar asignaciones: ' . $e->getMessage());
            return EXIT_ERROR;
        }

        CLI::newLine();
        CLI::write('Resumen:', 'cyan');
        CLI::write("  Creadas:  {$creadas}", 'green');
        CLI::write("  Omitidas: {$omitidas} (ya existían)", 'yellow');
        CLI::write("  Errores:  {$errores}", 'red');

        return EXIT_SUCCESS;
    }
}