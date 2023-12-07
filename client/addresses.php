<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    die();
}

$db = require "../database.php";

$stmt = $db->prepare('SELECT * FROM addresses WHERE is_deleted = 0');
$stmt->execute();

$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>Nazwa</th>
                    <th>Telefon</th>
                    <th>Adres</th>
                    <th>Miasto</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($addresses as $address): ?>
                    <tr>
                        <td><?= $address['name'] ?></td>
                        <td><?= $address['phone'] ?></td>
                        <td><?= $address['line'] ?></td>
                        <td><?= $address['city'] ?> <?= $address['zip_code'] ?></td>
                        <td class="btn-container">
                            <a class="btn btn-yellow"
                               href="/client/edit-address.php?id=<?= $address['id'] ?>">Edytuj</a>
                            <a class="btn btn-red" href="/client/delete-address.php?id=<?= $address['id'] ?>">Usuń</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="btn-container">
            <a class="btn btn-green" href="/client/create-address.php">Dodaj</a>
        </div>
    </div>
</body>
</html>