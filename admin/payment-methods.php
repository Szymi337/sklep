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

$stmt = $db->prepare('SELECT * FROM payment_methods WHERE is_deleted = 0');
$stmt->execute();

$paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        <?php if (count($paymentMethods) > 0): ?>
            <table class="table">
                <thead>
                <tr>
                    <th>Nazwa</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($paymentMethods as $category): ?>
                    <tr>
                        <td><?= $category['name'] ?></td>
                        <td class="btn-container">
                            <a class="btn btn-red"
                               href="/admin/delete-payment-method.php?<?= http_build_query(['id' => $category['id']]) ?>">
                                Usuń
                            </a>
                            <a class="btn btn-yellow"
                               href="/admin/edit-payment-method.php?<?= http_build_query(['id' => $category['id']]) ?>">
                                Edytuj
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-centered">
                Brak sposobów płatności
            </div>
        <?php endif; ?>

        <div class="btn-container">
            <a href="/admin/create-payment-method.php" class="btn">Dodaj</a>
        </div>
    </div>
</div>
</body>
</html>
