<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear TuDu</title>
</head>
<body>
    <form action="/tudu/create" method="post">
        <?= view('tudu/_form', ['op' =>'Crear']) ?>
    </form>
</body>
</html>