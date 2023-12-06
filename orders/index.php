<?php

session_start();

if (empty($_GET['id'])) {
    header('Location: /');
    exit;
}

$db = require "../database.php";

$stmt = $db->prepare('SELECT * FROM orders WHERE id = :id AND is_deleted = 0');
$stmt->execute(['id' => $_GET['id']]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: /');
    exit;
}

$stmt = $db->prepare('SELECT p.id, p.name, o.quantity, o.price FROM order_items o INNER JOIN products p ON p.id = o.product_id WHERE order_id = :order_id');
$stmt->execute(['order_id' => $order['id']]);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare('SELECT * FROM addresses WHERE id = :id');
$stmt->execute(['id' => $order['delivery_address_id']]);

$deliveryAddress = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare('SELECT * FROM addresses WHERE id = :id');
$stmt->execute(['id' => $order['address_id']]);

$address = $stmt->fetch(PDO::FETCH_ASSOC);

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
                    <th>Ilość</th>
                    <th>Cena</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['name'] ?></td>
                        <td><?= $product['quantity'] ?></td>
                        <td><?= number_format($product['price'] * $product['quantity'], 2, ',', ' ') ?>zł</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form">
            <h2>Adres dostawy</h2>

            <div class="address">
                <div class="address-name"><?= $deliveryAddress['name'] ?></div>
                <div class="address-line"><?= $deliveryAddress['line'] ?></div>
                <div class="address-zip-code"><?= $deliveryAddress['zip_code'] ?></div>
                <div class="address-city"><?= $deliveryAddress['city'] ?></div>
            </div>

            <h2>Adres zamawiającego</h2>

            <div class="address">
                <div class="address-name"><?= $address['name'] ?></div>
                <div class="address-line"><?= $address['line'] ?></div>
                <div class="address-zip-code"><?= $address['zip_code'] ?></div>
                <div class="address-city"><?= $address['city'] ?></div>
            </div>
        </div>

        <div class="btn-container">

        </div>
    </div>
</body>
</html>