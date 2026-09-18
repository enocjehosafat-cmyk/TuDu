<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tudu</title>
</head>

<body>
    <h1>Listado TuDu
    </h1>


    <a href="/tudu/new">Crear</a>
    <table>

        <tr>
            <th>
                id
            </th>
            <th>
                Titulo
            </th>
            <th>
                Descripcion
            </th>
            <th>
                Opciones
            </th>
        </tr>
         <?php foreach ($tudu as $key => $p) : ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= $p['titulo'] ?></td>
                <td><?= $p['descripcion'] ?></td>
                <td>
                    <a href="/tudu/show/<?= $p['id'] ?>">Show</a>
                    <a href="/tudu/edit/<?= $p['id'] ?>">Edit</a>
                    <form action="/tudu/delete/<?= $p['id'] ?>" method="post">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach ?>
      
   
    </table>   
</body>

</html>
 