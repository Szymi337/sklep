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

$id = ($_SERVER['REQUEST_METHOD'] === "POST") ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null);
if (!$id) {
    header("Location: /admin/categories.php");
    die();
}

$stmt = $db->prepare("SELECT * FROM delivery_methods WHERE id = :id AND is_deleted = 0");
$stmt->execute(['id' => $id]);

$deliveryMethod = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deliveryMethod) {
    header("Location: /admin/categories.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (!is_string($_POST['name'] ?? null)) {
        header("Location: /admin/edit-category.php?" . http_build_query([
                'error' => 'Pole nazwa jest wymagane.', 'name' => $_POST['name']
            ]));
        die();
    }

    $name = trim($_POST['name']);

    if (strlen($name) < 3) {
        header("Location: /admin/edit-category.php?" . http_build_query([
                'error' => 'Nazwa musi mieć minimum 3 znaki.', 'name' => $_POST['name']
            ]));
        die();
    }

    if (strlen($name) > 64) {
        header("Location: /admin/edit-category.php?" . http_build_query([
                'error' => 'Nazwa nie może być dłuższa niż 64 znaki.', 'name' => $_POST['name']
            ]));
        die();
    }

    if (!is_numeric($_POST['price'] ?? null)) {
        header("Location: /admin/edit-category.php?" . http_build_query([
                'error' => 'Pole cena jest wymagane.', 'name' => $_POST['name']
            ]));
        die();
    }

    $price = trim($_POST['price']);
    $price = floatval($price);

    if ($price < 0) {
        header("Location: /admin/edit-category.php?" . http_build_query([
                'error' => 'Cena nie może być mniejsza niż 0.', 'name' => $_POST['name']
            ]));
        die();
    }

    if ($price > 9999.99) {
        header("Location: /admin/edit-category.php?" . http_build_query([
                'error' => 'Cena nie może być większa niż 9999.99.', 'name' => $_POST['name']
            ]));
        die();
    }

    $db->beginTransaction();

    $stmt = $db->prepare("SELECT * FROM delivery_methods WHERE name = :name AND id != :id AND is_deleted = 0");
    $stmt->execute(['name' => $name, 'id' => $id]);

    $deliveryMethod = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($deliveryMethod) {
        header("Location: /admin/edit-delivery-method.php?" . http_build_query([
                'id' => $id, 'error' => 'Metoda dostawy o takiej nazwie już istnieje.', 'name' => $_POST['name']
            ]));
        die();
    }

    $stmt = $db->prepare("UPDATE delivery_methods SET name = :name, price = :price WHERE id = :id");
    $stmt->execute(['name' => $name, 'price' => $price, 'id' => $id]);

    $db->commit();

    header("Location: /admin/delivery-methods.php?" . http_build_query([
            'success' => 'Metoda dostawy została zaktualizowana.'
        ]));
    return;
}

if (empty($_GET['name']) && empty($_GET['price'])) {
    header("Location: /admin/edit-delivery-method.php?" . http_build_query([
            'id' => $id, 'name' => $deliveryMethod['name'], 'price' => $deliveryMethod['price']
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

    <form action="/admin/edit-delivery-method.php" method="POST" class="content form"
          enctype="multipart/form-data">
        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <input type="hidden" name="id" value="<?= $id ?>"/>

        <div class="input-container">
            <label for="name">Nazwa</label>
            <input type="text" id="name" name="name"
                   value="<?= htmlspecialchars($_GET['name'] ?? '') ?>"/>
        </div>

        <div class="input-container">
            <label for="price">Cena</label>
            <input type="number" id="price" name="price" step="0.01"
                   value="<?= htmlspecialchars($_GET['price'] ?? '') ?>"/>
        </div>

        <div class="btn-container">
            <button class="btn">Edytuj</button>
        </div>
    </form>
</div>
</body>
</html>