<?php

use Config\View;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar TuDu</title>
</head>
<body>
    <form action="/tudu/update/<?= $tudu['id'] ?>" method="post">
        <?= view('tudu/_form', ['op' =>'actualizar']) ?>
    </form>
</body>
</html>