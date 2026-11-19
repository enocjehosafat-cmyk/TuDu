<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Panel de mi empresa</h1>
            <p>
                Bienvenido, <?= esc(auth()->user()->username) ?>.
                <?php if ($empresa !== null): ?>
                    Empresa: <strong><?= esc($empresa['nombre']) ?></strong>.
                <?php endif; ?>
            </p>
        </div>
        <a class="crear tareas-boton-crear" href="<?= site_url('cliente/empleados/new') ?>">
            + Nuevo empleado
        </a>
    </div>

    <div class="personal-estadisticas">
        <div class="estadistica-tarjeta">
            <span>Total de empleados</span>
            <h2><?= esc($totalEmpleados) ?></h2>
        </div>
        <div class="estadistica-tarjeta">
            <span>Empleados activos</span>
            <h2 class="numero-activos"><?= esc($empleadosActivos) ?></h2>
        </div>
        <div class="estadistica-tarjeta">
            <span>Tareas registradas</span>
            <h2><?= esc($totalTareas) ?></h2>
        </div>
    </div>

    <div class="detalle" style="margin-bottom: 25px;">
        <h3>Acciones rápidas</h3>
        <p>Gestiona a tu equipo y consulta sus datos de acceso.</p>
        <a class="boton-crear" href="<?= site_url('cliente/empleados') ?>">Gestionar empleados</a>
    </div>

    <h3>Últimos empleados registrados</h3>
    <div class="table-responsive"><table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($empleados as $empleado): ?>
                <tr>
                    <td data-label="Nombre">
                        <strong><?= esc(trim($empleado['nombre'] . ' ' . $empleado['apellidos'])) ?></strong>
                    </td>
                    <td data-label="Usuario"><?= esc($empleado['username'] ?? '—') ?></td>
                    <td data-label="Correo"><?= esc($empleado['email'] ?? '—') ?></td>
                    <td data-label="Estatus">
                        <span class="estado-empleado <?= (int) $empleado['estatus'] === 1 ? 'activo' : 'inactivo' ?>">
                            <?= (int) $empleado['estatus'] === 1 ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($empleados === []): ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b;">Aún no hay empleados registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table></div>
<?= $this->endSection() ?>
