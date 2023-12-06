<?php

session_start();

$db = require __DIR__ . '/database.php';

if (!is_numeric($_GET['id'] ?? null)) {
    header('Location: /index.php');
    die();
}

$id = intval($_GET['id']);

$stmt = $db->prepare('SELECT * FROM custom_pages WHERE id = :id');
$stmt->execute(['id' => $id]);

$page = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$page) {
    header('Location: /index.php');
    die();
}

?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hiperventilation</title>
    <base href="/"/>

    <link rel="stylesheet" href="/css/app.css">

    <style>
        .page-title {
            font-size: 32px;
            line-height: 40px;
        }
    </style>
</head>
<body>
<div class="container">
    <?php require "navbar.php"; ?>

    <div class="content">
        <h1 class="page-title"><?= htmlspecialchars($page['name']) ?></h1>

        <pre><?= $page['content'] ?></pre>
    </div>
</div>
</body>
</html>
