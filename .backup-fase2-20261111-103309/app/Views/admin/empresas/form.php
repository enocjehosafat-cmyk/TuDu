<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="formulario">
        <h1><?= esc($title) ?></h1>

        <form action="<?= $empresa ? site_url('admin/empresas/update/' . $empresa['id']) : site_url('admin/empresas/create') ?>" method="post">
            <?= csrf_field() ?>

            <div class="campo">
                <label for="nombre">Nombre de la empresa *</label>
                <input type="text" name="nombre" id="nombre" value="<?= esc($empresa['nombre'] ?? old('nombre')) ?>" required>
            </div>

            <div class="campo">
                <label for="rfc">RFC</label>
                <input type="text" name="rfc" id="rfc" value="<?= esc($empresa['rfc'] ?? old('rfc')) ?>" maxlength="15">
            </div>

            <div class="campo">
                <label for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" value="<?= esc($empresa['direccion'] ?? old('direccion')) ?>" maxlength="255">
            </div>

            <button class="boton-crear" type="submit">Guardar empresa</button>
            <a href="<?= site_url('admin/empresas') ?>" style="margin-left: 10px;">Cancelar</a>
        </form>
    </div>
<?= $this->endSection() ?>
