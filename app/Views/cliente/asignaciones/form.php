<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <?php
        $oldTarea    = old('tarea_id', $asignacion['tarea_id'] ?? '');
        $oldEmpleado = old('empleado_id', $asignacion['empleado_id'] ?? '');
    ?>

    <div class="formulario">
        <h1><?= esc($title) ?></h1>

        <form action="<?= $asignacion !== null ? site_url('cliente/asignaciones/update/' . $asignacion['id']) : site_url('cliente/asignaciones/create') ?>" method="post">
            <?= csrf_field() ?>

            <div class="campo">
                <label for="tarea_id">Tarea *</label>
                <select name="tarea_id" id="tarea_id" required>
                    <option value="">Selecciona una tarea</option>
                    <?php foreach ($tareas as $tarea): ?>
                        <option value="<?= (int) $tarea['id'] ?>" <?= (string) $oldTarea === (string) $tarea['id'] ? 'selected' : '' ?>>
                            <?= esc($tarea['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($asignacion === null): ?>
                    <small>Las actividades de la tarea se copiarán automáticamente.</small>
                <?php endif; ?>
            </div>

            <div class="campo">
                <label for="empleado_id">Empleado *</label>
                <select name="empleado_id" id="empleado_id" required>
                    <option value="">Selecciona un empleado</option>
                    <?php foreach ($empleados as $empleado): ?>
                        <option value="<?= (int) $empleado['id'] ?>" <?= (string) $oldEmpleado === (string) $empleado['id'] ? 'selected' : '' ?>>
                            <?= esc(trim($empleado['nombre'] . ' ' . $empleado['apellidos'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="fecha">Fecha *</label>
                <input type="date" name="fecha" id="fecha" value="<?= esc(old('fecha', $asignacion['fecha'] ?? date('Y-m-d'))) ?>" required>
            </div>

            <div class="campo">
                <label for="fecha_vencimiento">Fecha de vencimiento (opcional)</label>
                <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" value="<?= esc(old('fecha_vencimiento', $asignacion['fecha_vencimiento'] ?? '')) ?>">
            </div>

            <div class="campo">
                <label for="prioridad">Prioridad *</label>
                <select name="prioridad" id="prioridad" required>
                    <option value="baja" <?= old('prioridad', $asignacion['prioridad'] ?? 'media') === 'baja' ? 'selected' : '' ?>>Baja</option>
                    <option value="media" <?= old('prioridad', $asignacion['prioridad'] ?? 'media') === 'media' ? 'selected' : '' ?>>Media</option>
                    <option value="alta" <?= old('prioridad', $asignacion['prioridad'] ?? 'media') === 'alta' ? 'selected' : '' ?>>Alta</option>
                </select>
            </div>

            <div class="campo">
                <label for="notas">Notas</label>
                <textarea name="notas" id="notas" maxlength="2000" rows="3"><?= esc(old('notas', $asignacion['notas'] ?? '')) ?></textarea>
            </div>

            <button class="boton-crear" type="submit"><?= $asignacion === null ? 'Crear asignación' : 'Guardar cambios' ?></button>
            <a href="<?= site_url('cliente/asignaciones') ?>" style="margin-left: 10px;">Cancelar</a>
        </form>
    </div>
<?= $this->endSection() ?>
