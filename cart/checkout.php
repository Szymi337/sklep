<?php

function html_addresses(string $prefix)
{ ?>
    <div class="form">
        <div class="input-container">
            <label for="<?= $prefix ?>name">Imię i nazwisko</label>
            <input type="text" id="<?= $prefix ?>name" name="<?= $prefix ?>name" placeholder="Jan Kowalski"
                   value="<?= htmlspecialchars($_GET[$prefix . 'name'] ?? '') ?>"/>
        </div>

        <div class="input-container">
            <label for="<?= $prefix ?>phone">Numer telefonu</label>
            <input type="text" id="<?= $prefix ?>phone" name="<?= $prefix ?>phone" placeholder="123456789"
                   value="<?= htmlspecialchars($_GET[$prefix . 'phone'] ?? '') ?>"/>
        </div>

        <div class="input-container">
            <label for="<?= $prefix ?>line">Adres</label>
            <input type="text" id="<?= $prefix ?>line" name="<?= $prefix ?>line" placeholder="ul. Przykładowa 1/2"
                   value="<?= htmlspecialchars($_GET[$prefix . 'line'] ?? '') ?>"/>
        </div>

        <div class="input-container">
            <label for="<?= $prefix ?>city">Miasto</label>
            <input type="text" id="<?= $prefix ?>city" name="<?= $prefix ?>city" placeholder="Warszawa"
                   value="<?= htmlspecialchars($_GET[$prefix . 'city'] ?? '') ?>"/>
        </div>

        <div class="input-container">
            <label for="<?= $prefix ?>zip_code">Kod pocztowy</label>
            <input type="text" id="<?= $prefix ?>zip_code" name="<?= $prefix ?>zip_code" placeholder="00-000"
                   value="<?= htmlspecialchars($_GET[$prefix . 'zip_code'] ?? '') ?>"/>
        </div>
    </div>
<?php }

if (empty($_GET['payment_method_id']) || empty($_GET['delivery_method_id'])) {
    header('Location: /cart/index.php?' . http_build_query([
            'error' => 'Wybierz metodę płatności i dostawy'
        ]));
    exit;
}

session_start();

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header('Location: /cart/index.php');
    exit;
}

$db = require __DIR__ . "/../database.php";

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

// adresy
$addresses = [];

if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT * FROM addresses WHERE user_id = :user_id AND is_deleted = 0');
    $stmt->execute(['user_id' => $_SESSION['user_id']]);

    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        .address-list {
            width: 100%;
            overflow-x: auto;

            display: flex;
            flex-direction: row;
            gap: 8px;
        }
    </style>
</head>
<body>
<div class="container">
    <?php require "../navbar.php"; ?>

    <form method="POST" action="/cart/buy.php" class="content form">
        <input type="hidden" name="payment_method_id"
               value="<?= htmlspecialchars($_GET['payment_method_id'] ?? '') ?>"/>
        <input type="hidden" name="delivery_method_id"
               value="<?= htmlspecialchars($_GET['delivery_method_id'] ?? '') ?>"/>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?= $_GET['error'] ?>
            </div>
        <?php endif; ?>

        <h2>Adres dostawy</h2>

        <?php foreach ($addresses as $address): ?>
            <div class="address-list">
                <div class="address">
                    <input type="radio" name="delivery_address_id" id="delivery_address_<?= $address['id'] ?>"
                           value="<?= $address['id'] ?>"
                        <?= $address['id'] == ($_GET['address_id'] ?? '') ? 'checked' : '' ?> />
                    <label for="delivery_address_<?= $address['id'] ?>">
                        <?= $address['name'] ?><br/>
                        <?= $address['phone'] ?><br/>
                        <?= $address['line'] ?><br/>
                        <?= $address['zip_code'] ?> <?= $address['city'] ?>
                    </label>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($addresses)): ?>
            <?php html_addresses('delivery_') ?>
        <?php endif; ?>

        <h2>Adres rozliczeniowy</h2>

        <?php foreach ($addresses as $address): ?>
            <div class="address-list">
                <div class="address">
                    <input type="radio" name="address_id" id="address_<?= $address['id'] ?>"
                           value="<?= $address['id'] ?>"
                        <?= $address['id'] == ($_GET['address_id'] ?? '') ? 'checked' : '' ?> />
                    <label for="address_<?= $address['id'] ?>">
                        <?= $address['name'] ?><br/>
                        <?= $address['phone'] ?><br/>
                        <?= $address['line'] ?><br/>
                        <?= $address['zip_code'] ?> <?= $address['city'] ?>
                    </label>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($addresses)): ?>
            <?php html_addresses('') ?>
        <?php endif; ?>

        <?php if (!empty($cart)): ?>
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