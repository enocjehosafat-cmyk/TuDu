<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <?php
        $actividades = old('actividades');
        if ($actividades === null && $tarea !== null) {
            $actividades = json_decode($tarea['actividades'] ?? '[]', true);
        }
        $actividades = is_array($actividades) ? $actividades : [];
    ?>

    <div class="formulario">
        <h1><?= esc($title) ?></h1>

        <form action="<?= $tarea !== null ? site_url('cliente/tareas/update/' . $tarea['id']) : site_url('cliente/tareas/create') ?>" method="post">
            <?= csrf_field() ?>

            <div class="campo">
                <label for="nombre">Nombre *</label>
                <input type="text" name="nombre" id="nombre" value="<?= esc(old('nombre', $tarea['nombre'] ?? '')) ?>" maxlength="100" required>
            </div>

            <div class="campo">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" maxlength="2000" rows="3"><?= esc(old('descripcion', $tarea['descripcion'] ?? '')) ?></textarea>
            </div>

            <div class="campo">
                <label for="tiempo_estimado">Tiempo estimado (minutos)</label>
                <input type="number" name="tiempo_estimado" id="tiempo_estimado" min="0" value="<?= esc(old('tiempo_estimado', $tarea['tiempo_estimado'] ?? '')) ?>">
            </div>

            <div class="campo">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="es_diaria" value="1" <?= old('es_diaria', $tarea['es_diaria'] ?? 0) ? 'checked' : '' ?>>
                    Es una tarea diaria (recurrente)
                </label>
            </div>

            <div class="campo">
                <label for="fecha_inicio">Fecha de inicio</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?= esc(old('fecha_inicio', $tarea['fecha_inicio'] ?? '')) ?>">
                <label for="fecha_fin" style="margin-top: 10px;">Fecha de fin (opcional)</label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="<?= esc(old('fecha_fin', $tarea['fecha_fin'] ?? '')) ?>">
            </div>

            <div class="campo">
                <label>Lista de Actividades (Checklist)</label>
                <p><small>Agrega los pasos que el empleado deberá seguir.</small></p>
                <div id="lista-actividades">
                    <?php foreach ($actividades as $act): ?>
                        <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                            <input type="text" name="actividades[]" value="<?= esc($act) ?>" style="flex: 1;" placeholder="Ej. Barrer el piso">
                            <button type="button" class="boton-delete" onclick="this.parentElement.remove()">X</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="boton-crear" onclick="agregarActividad()" style="margin-top: 10px; font-size: 12px;">+ Añadir actividad</button>
            </div>

            <button class="boton-crear" type="submit" style="width: 100%; margin-top: 20px;">Guardar Tarea</button>
            <a href="<?= site_url('cliente/tareas') ?>" style="display: block; text-align: center; margin-top: 10px;">Cancelar</a>
        </form>
    </div>

    <script>
        function agregarActividad() {
            const div = document.createElement('div');
            div.style = 'display: flex; gap: 8px; margin-bottom: 8px;';
            div.innerHTML = '<input type="text" name="actividades[]" style="flex: 1;" placeholder="Nueva actividad..."><button type="button" class="boton-delete" onclick="this.parentElement.remove()">X</button>';
            document.getElementById('lista-actividades').appendChild(div);
        }
    </script>
<?= $this->endSection() ?>
