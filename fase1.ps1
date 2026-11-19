# fase1.ps1 - Fase 1: Proteger historial y corregir eliminaciones
# Ejecutar desde C:\herd\tudu con: .\fase1.ps1

$ErrorActionPreference = 'Stop'

function Write-FileUtf8 {
    param([string]$Path, [string]$Content)
    $full = Join-Path $PWD $Path
    $dir  = Split-Path $full -Parent
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    $utf8NoBom = New-Object System.Text.UTF8Encoding $false
    [System.IO.File]::WriteAllText($full, $Content, $utf8NoBom)
    Write-Host "  [OK] $Path" -ForegroundColor Green
}

Write-Host "Aplicando Fase 1..." -ForegroundColor Cyan

# ──────────────────────────────────────────────────────────────
# 1) app/Views/cliente/asignaciones/index.php
# ──────────────────────────────────────────────────────────────
$v1 = @'
<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Asignaciones</h1>
            <p>Tareas asignadas a los empleados de tu empresa.</p>
        </div>
        <a class="crear tareas-boton-crear" href="<?= site_url('cliente/asignaciones/new') ?>">
            + Nueva asignación
        </a>
    </div>

    <div class="table-responsive"><table>
        <thead>
            <tr>
                <th>Tarea</th>
                <th>Empleado</th>
                <th>Fecha</th>
                <th>Vencimiento</th>
                <th>Prioridad</th>
                <th>Estatus</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($asignaciones as $asignacion): ?>
                <?php
                    $estatus          = $asignacion['estatus'] ?? 'pendiente';
                    $claseEstado      = match ($estatus) {
                        'completada' => 'activo',
                        'cancelada'  => 'inactivo',
                        default      => 'pendiente',
                    };
                    $tareaDesactivada = (int) ($asignacion['tarea_estatus'] ?? 1) === 0;
                ?>
                <tr>
                    <td data-label="Tarea">
                        <strong><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></strong>
                    </td>
                    <td data-label="Empleado"><?= esc($asignacion['empleado'] ?? '—') ?></td>
                    <td data-label="Fecha"><?= esc($asignacion['fecha'] ?? '—') ?></td>
                    <td data-label="Vencimiento"><?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></td>
                    <td data-label="Prioridad">
                        <span class="estado-empleado <?= (string) ($asignacion['prioridad'] ?? 'media') === 'alta' ? 'inactivo' : 'activo' ?>">
                            <?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?>
                        </span>
                    </td>
                    <td data-label="Estatus">
                        <span class="estado-empleado <?= esc($claseEstado) ?>">
                            <?= esc(ucfirst($estatus)) ?>
                        </span>
                        <?php if ($tareaDesactivada): ?>
                            <br><small style="color: #94a3b8;">(Tarea desactivada)</small>
                        <?php endif; ?>
                    </td>
                    <td data-label="Opciones" class="acciones">
                        <?php if ($estatus === 'cancelada'): ?>
                            <a class="boton-edit" href="<?= site_url('cliente/asignaciones/edit/' . $asignacion['id']) ?>">Ver</a>
                        <?php else: ?>
                            <a class="boton-edit" href="<?= site_url('cliente/asignaciones/edit/' . $asignacion['id']) ?>">Editar</a>
                            <form action="<?= site_url('cliente/asignaciones/delete/' . $asignacion['id']) ?>" method="post" onsubmit="return confirm('¿Cancelar esta asignación? El historial y el checklist se conservarán.');">
                                <?= csrf_field() ?>
                                <button class="boton-delete" type="submit">Cancelar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($asignaciones === []): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b;">Aún no hay asignaciones registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table></div>
<?= $this->endSection() ?>
'@
Write-FileUtf8 'app/Views/cliente/asignaciones/index.php' $v1

# ──────────────────────────────────────────────────────────────
# 2) app/Views/empleado/asignaciones/index.php
# ──────────────────────────────────────────────────────────────
$v2 = @'
<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Mis asignaciones</h1>
            <p>Tareas que te han asignado.</p>
        </div>
    </div>

    <div class="table-responsive"><table>
        <thead>
            <tr>
                <th>Tarea</th>
                <th>Fecha</th>
                <th>Vencimiento</th>
                <th>Prioridad</th>
                <th>Estatus</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($asignaciones as $asignacion): ?>
                <?php
                    $estatus          = $asignacion['estatus'] ?? 'pendiente';
                    $claseEstado      = match ($estatus) {
                        'completada' => 'activo',
                        'cancelada'  => 'inactivo',
                        default      => 'pendiente',
                    };
                    $tareaDesactivada = (int) ($asignacion['tarea_estatus'] ?? 1) === 0;
                ?>
                <tr>
                    <td data-label="Tarea">
                        <strong><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></strong>
                        <?php if (! empty($asignacion['descripcion'])): ?>
                            <br><small><?= esc(mb_strimwidth((string) $asignacion['descripcion'], 0, 60, '…')) ?></small>
                        <?php endif; ?>
                    </td>
                    <td data-label="Fecha"><?= esc($asignacion['fecha'] ?? '—') ?></td>
                    <td data-label="Vencimiento"><?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></td>
                    <td data-label="Prioridad"><?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?></td>
                    <td data-label="Estatus">
                        <span class="estado-empleado <?= esc($claseEstado) ?>">
                            <?= esc(ucfirst($estatus)) ?>
                        </span>
                        <?php if ($tareaDesactivada): ?>
                            <br><small style="color: #94a3b8;">(Tarea desactivada)</small>
                        <?php endif; ?>
                    </td>
                    <td data-label="Opciones" class="acciones">
                        <a class="boton-edit" href="<?= site_url('empleado/asignaciones/' . $asignacion['id']) ?>">
                            <?= $estatus === 'cancelada' ? 'Ver historial' : 'Ver detalle' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($asignaciones === []): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b;">No tienes tareas asignadas por el momento.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table></div>
<?= $this->endSection() ?>
'@
Write-FileUtf8 'app/Views/empleado/asignaciones/index.php' $v2

# ──────────────────────────────────────────────────────────────
# 3) app/Views/empleado/asignaciones/show.php
# ──────────────────────────────────────────────────────────────
$v3 = @'
<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <?php
        $estatus     = $asignacion['estatus'] ?? 'pendiente';
        $claseEstado = match ($estatus) {
            'completada' => 'activo',
            'cancelada'  => 'inactivo',
            default      => 'pendiente',
        };
        $soloLectura = $soloLectura ?? ($estatus === 'cancelada');
    ?>

    <div class="tareas-encabezado">
        <div>
            <h1><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></h1>
            <?php if (! empty($asignacion['descripcion'])): ?>
                <p><?= esc($asignacion['descripcion']) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= site_url('empleado/asignaciones') ?>">Volver a mis asignaciones</a>
    </div>

    <?php if ($soloLectura): ?>
        <div class="login-error" style="margin-bottom: 20px;">
            Esta asignación fue cancelada. Solo puedes consultar el historial.
        </div>
    <?php endif; ?>

    <div class="detalle">
        <p><strong>Fecha:</strong> <?= esc($asignacion['fecha'] ?? '—') ?></p>
        <p><strong>Vencimiento:</strong> <?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></p>
        <p><strong>Prioridad:</strong> <?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?></p>
        <p>
            <strong>Estatus:</strong>
            <span class="estado-empleado <?= esc($claseEstado) ?>">
                <?= esc(ucfirst($estatus)) ?>
            </span>
        </p>
        <?php if (! empty($asignacion['notas'])): ?>
            <p><strong>Notas:</strong> <?= esc($asignacion['notas']) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($checklist !== []): ?>
        <h3>Checklist</h3>
        <form action="<?= site_url('empleado/asignaciones/' . $asignacion['id'] . '/checklist') ?>" method="post">
            <?= csrf_field() ?>
            <fieldset <?= $soloLectura ? 'disabled' : '' ?> style="border: none; padding: 0; margin: 0;">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">OK</th>
                                <th>Paso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($checklist as $item): ?>
                                <tr <?= $soloLectura ? '' : 'onclick="this.querySelector(\'input\').click();" style="cursor: pointer;"' ?>>
                                    <td style="text-align: center;" onclick="event.stopPropagation();">
                                        <input type="checkbox" name="completada[]" value="<?= (int) $item['id'] ?>" <?= (int) $item['completada'] === 1 ? 'checked' : '' ?>>
                                    </td>
                                    <td>
                                        <?= esc($item['notas'] ?? '') ?>
                                        <?php if (! empty($item['fecha_completada'])): ?>
                                            <br><small style="color: #64748b;">Completado el <?= esc($item['fecha_completada']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </fieldset>
            <?php if (! $soloLectura): ?>
                <button class="boton-crear" type="submit" style="margin-top: 10px;">Guardar checklist</button>
            <?php endif; ?>
        </form>
    <?php else: ?>
        <p style="color: #64748b;">Esta asignación no tiene checklist.</p>
    <?php endif; ?>

    <?php if (! $soloLectura && $estatus !== 'completada'): ?>
        <form action="<?= site_url('empleado/asignaciones/' . $asignacion['id'] . '/completar') ?>" method="post" style="margin-top: 20px;">
            <?= csrf_field() ?>
            <button class="boton-crear" type="submit">Marcar como completada</button>
        </form>
    <?php endif; ?>
<?= $this->endSection() ?>
'@
Write-FileUtf8 'app/Views/empleado/asignaciones/show.php' $v3

# ──────────────────────────────────────────────────────────────
# 4) app/Controllers/Cliente/AsignacionController.php
# ──────────────────────────────────────────────────────────────
$c1 = @'
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
        $data = (new AsignacionTareaModel())->listarPaginado($this->empresaId());

        return view('cliente/asignaciones/index', [
            'title'        => 'Asignaciones',
            'asignaciones' => $data['items'],
            'total'        => $data['total'],
            'page'         => $data['page'],
            'per'          => $data['per'],
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
            (new ChecklistTareaModel())->crearItems((int) $asignacionId, $actividades);

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

        if (($asignacion['estatus'] ?? '') === 'cancelada') {
            return redirect()->to(site_url('cliente/asignaciones'))
                ->with('error', 'No puedes editar una asignación cancelada.');
        }

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

        if (($asignacion['estatus'] ?? '') === 'cancelada') {
            return redirect()->to(site_url('cliente/asignaciones'))
                ->with('error', 'No puedes editar una asignación cancelada.');
        }

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

        return redirect()->to(site_url('cliente/asignaciones'))
            ->with('mensaje', 'Asignación cancelada. El historial y el checklist se conservan.');
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
'@
Write-FileUtf8 'app/Controllers/Cliente/AsignacionController.php' $c1

# ──────────────────────────────────────────────────────────────
# 5) app/Controllers/Empleado/DashboardController.php
# ──────────────────────────────────────────────────────────────
$c2 = @'
<?php

namespace App\Controllers\Empleado;

use App\Controllers\BaseController;
use App\Models\EmpleadoModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $perfil = (new EmpleadoModel())
            ->where('usuario_id', auth()->id())
            ->first();

        if ($perfil === null) {
            return view('empleado/dashboard', [
                'title'        => 'Panel de empleado',
                'perfil'       => null,
                'asignaciones' => [],
                'pendientes'   => 0,
                'completadas'  => 0,
            ]);
        }

        $asignaciones = db_connect()->table('asignaciones_tareas a')
            ->select('a.*, t.nombre as tarea, t.descripcion')
            ->join('tareas t', 't.id = a.tarea_id', 'left')
            ->where('a.empleado_id', $perfil['id'])
            ->orderBy('a.fecha', 'DESC')
            ->get()
            ->getResultArray();

        $completadas = 0;
        $pendientes  = 0;

        foreach ($asignaciones as $a) {
            if ($a['estatus'] === 'completada') {
                $completadas++;
            } elseif ($a['estatus'] === 'pendiente') {
                $pendientes++;
            }
        }

        return view('empleado/dashboard', [
            'title'        => 'Panel de empleado',
            'perfil'       => $perfil,
            'asignaciones' => $asignaciones,
            'pendientes'   => $pendientes,
            'completadas'  => $completadas,
        ]);
    }
}
'@
Write-FileUtf8 'app/Controllers/Empleado/DashboardController.php' $c2

# ──────────────────────────────────────────────────────────────
# 6) app/Views/empleado/dashboard.php
# ──────────────────────────────────────────────────────────────
$v4 = @'
<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Mi panel</h1>
            <p>
                Bienvenido, <?= esc(auth()->user()->username) ?>.
                <?php if ($perfil !== null): ?>
                    <?= esc(trim($perfil['nombre'] . ' ' . $perfil['apellidos'])) ?>
                <?php endif; ?>
            </p>
        </div>
        <a class="crear tareas-boton-crear" href="<?= site_url('empleado/asignaciones') ?>">
            Ver mis asignaciones
        </a>
    </div>

    <?php if ($perfil === null): ?>
        <div class="detalle">
            <h3>Perfil sin configurar</h3>
            <p>Tu cuenta no tiene un perfil de empleado asociado. Contacta a tu empresa o al administrador.</p>
        </div>
    <?php else: ?>
        <div class="personal-estadisticas">
            <div class="estadistica-tarjeta">
                <span>Tareas pendientes</span>
                <h2 class="numero-inactivos"><?= esc($pendientes) ?></h2>
            </div>
            <div class="estadistica-tarjeta">
                <span>Tareas completadas</span>
                <h2 class="numero-activos"><?= esc($completadas) ?></h2>
            </div>
        </div>

        <h3>Mis asignaciones</h3>
        <div class="table-responsive"><table>
            <thead>
                <tr>
                    <th>Tarea</th>
                    <th>Fecha</th>
                    <th>Vencimiento</th>
                    <th>Prioridad</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asignaciones as $asignacion): ?>
                    <?php
                        $estatus     = $asignacion['estatus'] ?? 'pendiente';
                        $claseEstado = match ($estatus) {
                            'completada' => 'activo',
                            'cancelada'  => 'inactivo',
                            default      => 'pendiente',
                        };
                    ?>
                    <tr>
                        <td data-label="Tarea">
                            <strong><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></strong>
                            <?php if (! empty($asignacion['descripcion'])): ?>
                                <br><small><?= esc($asignacion['descripcion']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td data-label="Fecha"><?= esc($asignacion['fecha'] ?? '—') ?></td>
                        <td data-label="Vencimiento"><?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></td>
                        <td data-label="Prioridad"><?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?></td>
                        <td data-label="Estatus">
                            <span class="estado-empleado <?= esc($claseEstado) ?>">
                                <?= esc(ucfirst($estatus)) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if ($asignaciones === []): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b;">No tienes tareas asignadas por el momento.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
<?= $this->endSection() ?>
'@
Write-FileUtf8 'app/Views/empleado/dashboard.php' $v4

# ──────────────────────────────────────────────────────────────
# 7) public/css/style.css  → solo AGREGAR la clase nueva
# ──────────────────────────────────────────────────────────────
$cssAppend = @'

/* ===== Fase 1: estado pendiente (amarillo) ===== */
.estado-empleado.pendiente {
    background-color: #fef9c3;
    color: #854d0e;
}
'@
$cssPath = Join-Path $PWD 'public/css/style.css'
$current = [System.IO.File]::ReadAllText($cssPath)
if ($current -notmatch '\.estado-empleado\.pendiente') {
    $utf8NoBom = New-Object System.Text.UTF8Encoding $false
    [System.IO.File]::WriteAllText($cssPath, $current + $cssAppend, $utf8NoBom)
    Write-Host "  [OK] public/css/style.css (clase agregada)" -ForegroundColor Green
} else {
    Write-Host "  [SKIP] public/css/style.css ya tenía la clase" -ForegroundColor Yellow
}

# ──────────────────────────────────────────────────────────────
# 8) Validar sintaxis PHP
# ──────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "Validando sintaxis PHP..." -ForegroundColor Cyan
php -l app/Controllers/Cliente/AsignacionController.php
php -l app/Controllers/Empleado/DashboardController.php

# ──────────────────────────────────────────────────────────────
# 9) Limpiar caché de vistas
# ──────────────────────────────────────────────────────────────
if (Test-Path writable/cache) {
    Get-ChildItem writable/cache -File | Where-Object { $_.Name -ne 'index.html' } | Remove-Item -Force -ErrorAction SilentlyContinue
    Write-Host "  [OK] Caché de vistas limpiado" -ForegroundColor Green
}

Write-Host ""
Write-Host "Fase 1 aplicada. Revisa la app en el navegador." -ForegroundColor Cyan
Write-Host "Si algo falla, restaura con: .\restore-fase1.ps1" -ForegroundColor Yellow