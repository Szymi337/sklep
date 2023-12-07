<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    die();
}

$db = require "../database.php";

$stmt = $db->prepare('SELECT * FROM orders WHERE is_deleted = 0');
$stmt->execute();

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$orders = array_map(function ($order) use ($db) {
    $stmt = $db->prepare('SELECT p.id, o.price, o.quantity FROM order_items o INNER JOIN products p ON p.id = o.product_id WHERE order_id = :order_id');
    $stmt->execute(['order_id' => $order['id']]);

    $order['final_price'] = 0;

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $order['final_price'] += $item['price'] * $item['quantity'];
    }

    return $order;
}, $orders);

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
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>Nazwa</th>
                    <th>Cena</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>Zamówienie <?= $order['id'] ?></td>
                        <td><?= number_format($order['final_price'], 2, ',', ' ') ?>zł</td>
                        <td class="btn-container">
                            <a href="/orders/index.php?id=<?= $order['id'] ?>" class="btn">Szczegóły</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>