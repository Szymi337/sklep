<?php

session_start();

$db = require __DIR__ . "/../database.php";

$cart = $_SESSION['cart'] ?? [];

$cart = array_map(function ($itemId) use ($cart, $db) {
    $stmt = $db->prepare('SELECT * FROM products WHERE id = :id AND is_deleted = 0');
    $stmt->execute(['id' => $itemId]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        unset($_SESSION['cart'][$itemId]);
        return null;
    }

    $product['quantity'] = $cart[$itemId];
    $product['final_price'] = $product['price'] * $product['quantity'];
    $product['final_price_display'] = number_format($product['final_price'], 2, ',', ' ') . 'zł';
    $product['price_display'] = number_format($product['price'], 2, ',', ' ');

    return $product;
}, array_keys($cart));

$cart = array_filter($cart);

$finalPrice = array_sum(array_column($cart, 'final_price'));

// metody płatności
$stmt = $db->prepare('SELECT * FROM payment_methods WHERE is_deleted = 0');
$stmt->execute();

$paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// metody dostawy
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

    <form method="GET" action="/cart/checkout.php" class="content form">
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?= $_GET['error'] ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>Nazwa</th>
                    <th>Ilość</th>
                    <th>Cena</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($cart as $product): ?>
                    <tr>
                        <td><?= $product['name'] ?></td>
                        <td><?= $product['quantity'] ?></td>
                        <td><?= $product['final_price_display'] ?></td>
                        <td class="btn-container" style="width: auto;">
                            <a href="/cart/add.php?<?= http_build_query([
                                'id' => $product['id'],
                                'back_url' => $_SERVER['REQUEST_URI']
                            ]) ?>" class="btn btn-green">Dodaj</a>

                            <a href="/cart/remove.php?<?= http_build_query([
                                'id' => $product['id'],
                                'back_url' => $_SERVER['REQUEST_URI']
                            ]) ?>" class="btn btn-red">Usuń</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($cart)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">Brak produktów w koszyku</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($cart)): ?>
            <h2>Metoda płatności</h2>

            <div class="input-container">
                <?php foreach ($paymentMethods as $paymentMethod): ?>
                    <div class="input-container-radio">
                        <input type="radio" name="payment_method_id"
                               id="payment_method_<?= $paymentMethod['id'] ?>"
                               value="<?= $paymentMethod['id'] ?>"
                            <?= $paymentMethod['id'] == ($_GET['payment_method_id'] ?? '') ? 'checked' : '' ?>/>
                        <label for="payment_method_<?= $paymentMethod['id'] ?>"><?= $paymentMethod['name'] ?></label>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($paymentMethods)): ?>
                    <div class="error">Brak metod płatności, administrator nie dodał żadnych.</div>
                <?php endif; ?>
            </div>

            <h2>Metoda dostawy</h2>

            <div class="input-container">
                <?php foreach ($deliveryMethods as $deliveryMethod): ?>
                    <div class="input-container-radio">
                        <input type="radio" name="delivery_method_id" id="delivery_method_<?= $deliveryMethod['id'] ?>"
                               value="<?= $deliveryMethod['id'] ?>"
                            <?= $deliveryMethod['id'] == ($_GET['delivery_method_id'] ?? '') ? 'checked' : '' ?>/>
                        <label for="delivery_method_<?= $deliveryMethod['id'] ?>"><?= $deliveryMethod['name'] ?></label>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($deliveryMethods)): ?>
                    <div class="error">Brak metod dostawy, administrator nie dodał żadnych.</div>
                <?php endif; ?>
            </div>

            <div class="btn-container">
                <div class="total-price">
                    <span>Suma: <?= number_format($finalPrice, 2, ',', ' ') ?>zł</span>
                </div>
                <button type="submit" class="btn btn-white">Ustaw dane adresowe</button>
            </div>
        <?php endif; ?>
    </form>
</div>
</body>
</html>