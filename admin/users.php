<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    die();
}

$db = require "../database.php";
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: ../index.php");
    die();
}

if ($user['is_admin'] !== 1) {
    header("Location: ../index.php");
    die();
}

$stmt = $db->prepare('SELECT * FROM users');
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
</head>
<body>
<div class="container">
    <?php require "../navbar.php"; ?>

    <div class="content form">
        <?php if (isset($_GET['success'])): ?>
            <div class="success">
                <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>

        <?php if (count($users) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Nazwa</th>
                        <th>Email</th>
                        <th>Admin</th>
                        <th>Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['login'] ?></td>
                            <td><?= $user['email'] ?></td>
                            <td><?= $user['is_admin'] ? 'Tak' : 'Nie' ?></td>
                            <td class="btn-container">
                                <a class="btn btn-red"
                                   href="/admin/delete-user.php?<?= http_build_query(['id' => $user['id']]) ?>">
                                    Usuń
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-centered">
                Brak stron
            </div>
        <?php endif; ?>

        <div class="btn-container">
            <a href="/admin/create-user.php" class="btn">Dodaj admina</a>
        </div>
    </div>
</div>
</body>
</html>
