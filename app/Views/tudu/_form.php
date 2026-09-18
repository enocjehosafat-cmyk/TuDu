<label for="titulo">Titulo</label>
<input type="text" name="titulo" placeholder="Titulo" id="titulo" value="<?= $tudu['titulo'] ?>">
<label for="descripcion">Descrupcion</label>
<textarea name="descripcion" id="descripcion">
            <?= $tudu['descripcion'] ?>
        </textarea>
<button type="sumit"><?= $op ?></button>