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

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (!is_string($_POST['name'] ?? null)) {
        header("Location: /admin/create-delivery-method.php?" . http_build_query([
                'error' => 'Uzupełnij wszystkie pola.',
                'name' => $_POST['name']
            ]));
        die();
    }

    $name = trim($_POST['name']);

    if (strlen($name) < 3) {
        header("Location: /admin/create-delivery-method.php?" . http_build_query([
                'error' => 'Nazwa musi mieć minimum 3 znaki.',
                'name' => $_POST['name']
            ]));
        die();
    }

    if (strlen($name) > 64) {
        header("Location: /admin/create-delivery-method.php?" . http_build_query([
                'error' => 'Nazwa nie może być dłuższa niż 64 znaki.',
                'name' => $_POST['name']
            ]));
        die();
    }

    if (!is_numeric($_POST['price'] ?? null)) {
        header("Location: /admin/create-delivery-method.php?" . http_build_query([
                'error' => 'Uzupełnij wszystkie pola.',
                'name' => $_POST['name'],
                'price' => $_POST['price']
            ]));
        die();
    }

    $price = trim($_POST['price']);
    $price = floatval($price);

    if ($price < 0) {
        header("Location: /admin/create-delivery-method.php?" . http_build_query([
                'error' => 'Cena nie może być ujemna.',
                'name' => $_POST['name'],
                'price' => $_POST['price']
            ]));
        die();
    }

    if ($price > 9999.99) {
        header("Location: /admin/create-delivery-method.php?" . http_build_query([
                'error' => 'Cena nie może być większa niż 1000000.',
                'name' => $_POST['name'],
                'price' => $_POST['price']
            ]));
        die();
    }

    $stmt = $db->prepare("SELECT * FROM delivery_methods WHERE name = :name AND is_deleted = 0;");
    $stmt->execute(['name' => $name]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        header("Location: /admin/create-delivery-method.php?" . http_build_query([
                'error' => 'Sposób dostawy o takiej nazwie już istnieje.',
                'name' => $_POST['name']
            ]));
        die();
    }

    $stmt = $db->prepare("INSERT INTO delivery_methods (name, price) VALUES (:name, :price)");
    $stmt->execute(['name' => $name, 'price' => $price]);

    header("Location: /admin/delivery-methods.php?" . http_build_query([
            'success' => 'Sposób dostawy został dodany.'
        ]));
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

</head>
<body>
<div class="container">
    <?php require "../navbar.php"; ?>

    <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" class="content form">
        <h2>Dodaj nowy sposób dostawy</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <div class="input-container">
            <label for="name">Nazwa</label>
            <input type="text" id="name" name="name"
                   value="<?= htmlspecialchars($_GET['name'] ?? '') ?>"/>
        </div>

        <div class="input-container">
            <label for="name">Cena</label>
            <input type="number" id="price" name="price" step="0.01"
                   value="<?= htmlspecialchars($_GET['price'] ?? '') ?>"/>
        </div>

        <div class="btn-container">
            <button class="btn">Dodaj</button>
        </div>
    </form>
</div>
</body>
</html>

