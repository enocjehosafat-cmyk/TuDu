<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias</title>
</head>

<body>
    <h1>Listado Categorias
    </h1>


    <a href="/categoria/new">Crear</a>
    <table>

        <tr>
            <th>
                id
            </th>
            <th>
                Titulo
            </th>
            <th>
                Opciones
            </th>
        </tr>
         <?php foreach ($categoria as $key => $p) : ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= $p['titulo'] ?></td>
                <td>
                    <a href="/categoria/show/<?= $p['id'] ?>">Show</a>
                    <a href="/categoria/edit/<?= $p['id'] ?>">Edit</a>
                    <form action="/categoria/delete/<?= $p['id'] ?>" method="post">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach ?>
      
   
    </table>   
</body>

</html>
 