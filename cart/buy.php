<?php

function validate_address(string $prefix): ?string
{
    $errors = [];

    if (empty($_POST[$prefix . 'name'])) {
        $errors[] = 'Imię i nazwisko jest wymagane';
    }

    if (empty($_POST[$prefix . 'phone'])) {
        $errors[] = 'Numer telefonu jest wymagany';
    }

    if (empty($_POST[$prefix . 'line'])) {
        $errors[] = 'Adres jest wymagany';
    }

    if (empty($_POST[$prefix . 'zip_code'])) {
        $errors[] = 'Kod pocztowy jest wymagany';
    }

    if (empty($_POST[$prefix . 'city'])) {
        $errors[] = 'Miasto jest wymagane';
    }

    if (!empty($errors)) {
        return implode('<br>', $errors);
    }

    if (!preg_match('/^[0-9]{2}-[0-9]{3}$/', $_POST[$prefix . 'zip_code'])) {
        $errors[] = 'Kod pocztowy jest niepoprawny';
    }

    if (!preg_match('/^[0-9]{9}$/', $_POST[$prefix . 'phone'])) {
        $errors[] = 'Numer telefonu jest niepoprawny';
    }

    if (strlen($_POST[$prefix . 'name']) > 255) {
        $errors[] = 'Imię i nazwisko jest za długie';
    }

    if (strlen($_POST[$prefix . 'phone']) > 255) {
        $errors[] = 'Numer telefonu jest za długi';
    }

    if (strlen($_POST[$prefix . 'line']) > 255) {
        $errors[] = 'Adres jest za długi';
    }

    if (strlen($_POST[$prefix . 'zip_code']) > 255) {
        $errors[] = 'Kod pocztowy jest za długi';
    }

    if (strlen($_POST[$prefix . 'city']) > 255) {
        $errors[] = 'Miasto jest za długie';
    }

    if (!empty($errors)) {
        return implode('<br>', $errors);
    }

    return null;
}

session_start();

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header('Location: /cart/checkout.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header('Location: /cart/index.php');
    exit;
}

$db = require __DIR__ . "/../database.php";

$db->beginTransaction();

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

    return $product;
}, array_keys($cart));

$cart = array_filter($cart);

$finalPrice = array_sum(array_column($cart, 'final_price'));

$addresses = [];

if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT * FROM addresses WHERE user_id = :user_id AND is_deleted = 0');
    $stmt->execute(['user_id' => $_SESSION['user_id']]);

    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($addresses)) {
    if (($addressErrors = validate_address('')) !== null) {
        header('Location: /cart/checkout.php?' . http_build_query($_POST + ['error' => $addressErrors]));
        die();
    }

    if (($deliveryAddressErrors = validate_address('delivery_')) !== null) {
        header('Location: /cart/checkout.php?' . http_build_query($_POST + ['error' => $deliveryAddressErrors]));
        die();
    }

    $sql = <<<SQL
INSERT INTO addresses
    (user_id, name, phone, line, zip_code, city)
VALUES
    (:user_id, :name, :phone, :line, :zip_code, :city);
SQL;

    $stmt = $db->prepare($sql);
    $stmt->execute([
        'user_id' => $_SESSION['user_id'] ?? null,
        'name' => $_POST['name'],
        'phone' => $_POST['phone'],
        'line' => $_POST['line'],
        'zip_code' => $_POST['zip_code'],
        'city' => $_POST['city'],
    ]);

    $addressId = $db->lastInsertId();

    $stmt->execute([
        'user_id' => $_SESSION['user_id'] ?? null,
        'name' => $_POST['delivery_name'],
        'phone' => $_POST['delivery_phone'],
        'line' => $_POST['delivery_line'],
        'zip_code' => $_POST['delivery_zip_code'],
        'city' => $_POST['delivery_city'],
    ]);

    $deliveryAddressId = $db->lastInsertId();
} else {
    if (empty($_POST['address_id'])) {
        header('Location: /cart/checkout.php?' . http_build_query($_POST + ['error' => 'Wybierz adres rozliczeniowy.']));
        die();
    }

    if (empty($_POST['delivery_address_id'])) {
        header('Location: /cart/checkout.php?' . http_build_query($_POST + ['error' => 'Wybierz adres dostawy.']));
        die();
    }

    $addressId = $_POST['address_id'];
    $deliveryAddressId = $_POST['delivery_address_id'];

    $stmt = $db->prepare('SELECT * FROM addresses WHERE id = :id AND is_deleted = 0');
    $stmt->execute(['id' => $addressId]);

    if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
        header('Location: /cart/checkout.php?' . http_build_query($_POST + ['error' => 'Wybrany adres nie istnieje']));
        die();
    }

    $stmt = $db->prepare('SELECT * FROM addresses WHERE id = :id AND is_deleted = 0');
    $stmt->execute(['id' => $deliveryAddressId]);

    if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
        header('Location: /cart/checkout.php?' . http_build_query($_POST + ['error' => 'Wybrany adres dostawy nie istnieje']));
        die();
    }
}

if (empty($_POST['payment_method_id'])) {
    header('Location: /cart/checkout.php?' . http_build_query($_POST + ['error' => 'Wybierz metodę płatności']));
    die();
}

if (empty($_POST['delivery_method_id'])) {
    header('Location: /cart/checkout.php?' . http_build_query($_POST + ['error' => 'Wybierz metodę dostawy']));
    die();
}

$stmt = $db->prepare('SELECT * FROM payment_methods WHERE id = :id');
$stmt->execute(['id' => $_POST['payment_method_id']]);

if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
    header('Location: /cart/checkout.php?' . http_build_query($_POST + ['error' => 'Wybrana metoda płatności nie istnieje']));
    die();
}

$stmt = $db->prepare('SELECT * FROM delivery_methods WHERE id = :id');
$stmt->execute(['id' => $_POST['delivery_method_id']]);

if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
    header('Location: /cart/checkout.php?' . http_build_query($_POST + ['error' => 'Wybrana metoda dostawy nie istnieje']));
    die();
}


$sql = <<<SQL
INSERT INTO orders 
    (user_id, payment_method_id, delivery_method_id,
     address_id, delivery_address_id, status) 
VALUES
    (:user_id, :payment_method_id, :delivery_method_id,
     :address_id, :delivery_address_id, :status);
SQL;

$stmt = $db->prepare($sql);
$stmt->execute([
    'user_id' => $_SESSION['user_id'] ?? null,
    'payment_method_id' => $_POST['payment_method_id'],
    'delivery_method_id' => $_POST['delivery_method_id'],
    'address_id' => $addressId,
    'delivery_address_id' => $deliveryAddressId,
    'status' => 'Nowe zamówienie',
]);

$id = $db->lastInsertId();

$sql = <<<SQL
INSERT INTO order_items 
    (order_id, product_id, quantity, price)
VALUES
    (:order_id, :product_id, :quantity, :price);
SQL;

$stmt = $db->prepare($sql);

foreach ($cart as $product) {
    $stmt->execute([
        'order_id' => $id,
        'product_id' => $product['id'],
        'quantity' => $product['quantity'],
        'price' => $product['price'],
    ]);
}

$db->commit();

unset($_SESSION['cart']);

header('Location: /orders/index.php?' . http_build_query([
        'success' => 'Zamówienie zostało złożone',
        'id' => $id
    ]));