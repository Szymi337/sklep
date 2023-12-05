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

$stmt = $db->prepare('SELECT * FROM delivery_methods WHERE is_deleted = 0');
$stmt->execute();

$deliveryMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    <div class="content">
        <?php if (isset($_GET['success'])): ?>
            <div class="success">
                <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>

        <?php if (count($deliveryMethods) > 0): ?>
            <table class="table">
                <thead>
                <tr>
                    <th>Nazwa</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($deliveryMethods as $deliveryMethod): ?>
                    <tr>
                        <td><?= $deliveryMethod['name'] ?> - <?= $deliveryMethod['price'] ?>zł</td>
                        <td class="btn-container">
                            <a class="btn btn-red"
                               href="/admin/delete-delivery-method.php?<?= http_build_query(['id' => $deliveryMethod['id']]) ?>">
                                Usuń
                            </a>
                            <a class="btn btn-yellow"
                               href="/admin/edit-delivery-method.php?<?= http_build_query(['id' => $deliveryMethod['id']]) ?>">
                                Edytuj
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-centered">
                Brak kategorii
            </div>
        <?php endif; ?>

        <div class="btn-container">
            <a href="/admin/create-delivery-method.php" class="btn">Dodaj</a>
        </div>
    </div>
</div>
</body>
</html>
