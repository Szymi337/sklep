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

//

$id = ($_SERVER['REQUEST_METHOD'] === "POST") ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null);
if (!is_numeric($id)) {
    header("Location: /admin/products.php");
    die();
}

$id = intval($id);

$stmt = $db->prepare("SELECT * FROM products WHERE id = :id AND is_deleted = 0");
$stmt->execute(['id' => $id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: /admin/products.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $oldInput = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'category_id' => $_POST['category_id'] ?? '',
        'price' => $_POST['price'] ?? 0,
    ];

    if (empty($_POST['name']) || empty($_POST['price']) || empty($_POST['category_id'])) {
        header("Location: /admin/edit-product.php?" . http_build_query([
                'error' => 'Uzupełnij wszystkie pola.',
                ...$oldInput
            ]));
        die();
    }

    if (!is_string($_POST['name']) || strlen($_POST['name']) < 3) {
        header("Location: /admin/edit-product.php?" . http_build_query([
                'error' => 'Nazwa musi mieć minimum 3 znaki.',
                ...$oldInput
            ]));
        die();
    }

    if (!is_string($_POST['description'])) {
        header("Location: /admin/edit-product.php?" . http_build_query([
                'error' => 'Opis musi być ciągiem znaków.',
                ...$oldInput
            ]));
        die();
    }

    if (strlen($_POST['description']) > 2500) {
        header("Location: /admin/edit-product.php?" . http_build_query([
                'error' => 'Opis nie może być dłuższy niż 2500 znaków.',
                ...$oldInput
            ]));
        die();
    }

    if (!is_numeric($_POST['price'])) {
        header("Location: /admin/edit-product.php?" . http_build_query([
                'error' => 'Cena musi być liczbą.',
                ...$oldInput
            ]));
        die();
    }

    if ($_POST['price'] > 9999.99) {
        header("Location: /admin/edit-product.php?" . http_build_query([
                'error' => 'Cena nie może być większa niż 9999.99.',
                ...$oldInput
            ]));
        die();
    }

    if ($_POST['price'] < 0) {
        header("Location: /admin/edit-product.php?" . http_build_query([
                'error' => 'Cena nie może być mniejsza niż 0.',
                ...$oldInput
            ]));
        die();
    }

    $db->beginTransaction();

    $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id AND is_deleted = 0");
    $stmt->execute(['id' => $_POST['category_id']]);

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        header("Location: /admin/edit-product.php?" . http_build_query([
                'error' => 'Wybrana kategoria nie istnieje.',
                ...$oldInput
            ]));
        die();
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $stmt = $db->prepare("UPDATE products SET name = :name, description = :description, price = :price WHERE id = :id");
        $stmt->execute([
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'id' => $id
        ]);
    } else {
        $image = $_FILES['image'];

        $imageFile = uniqid() . "." . pathinfo($image['name'], PATHINFO_EXTENSION);
        $imagePath = "/../images/" . $imageFile;

        move_uploaded_file($image['tmp_name'], __DIR__ . $imagePath);

        $stmt = $db->prepare("UPDATE products SET name = :name, description = :description, price = :price, image = :image WHERE id = :id");
        $stmt->execute([
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'image' => $imageFile,
            'id' => $id
        ]);
    }


    $db->commit();

    header("Location: /admin/products.php");
    return;
}

if (empty($_GET['price']) && empty($_GET['name']) && empty($_GET['description']) && empty($_GET['category_id'])) {
    header("Location: /admin/edit-product.php?" . http_build_query([
            'id' => $id,
            'name' => $product['name'],
            'description' => $product['description'],
            'category_id' => $product['category_id'],
            'price' => $product['price'],
        ]));
    die();
}

$stmt = $db->prepare("SELECT * FROM categories WHERE is_deleted = 0");
$stmt->execute();

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        .edit-product-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <?php require "../navbar.php"; ?>

    <form action="/admin/edit-product.php" method="POST" class="content edit-product-form"
          enctype="multipart/form-data">
        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <input type="hidden" name="id" value="<?= $id ?>"/>

        <div class="input-container">
            <label for="image">Zdjęcie</label>
            <input type="file" id="image" name="image"/>
        </div>

        <div class="input-container">
            <label for="name">Nazwa</label>
            <input type="text" id="name" name="name"
                   value="<?= htmlspecialchars($_GET['name'] ?? '') ?>"/>
        </div>

        <div class="input-container">
            <label for="description">Opis</label>
            <textarea name="description" id="description" cols="30"
                      rows="10"><?= htmlspecialchars($_GET['description'] ?? '') ?></textarea>
        </div>

        <div class="input-container">
            <label for="category_id">Kategoria</label>
            <select name="category_id" id="category_id">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"
                        <?= $category['id'] === ($_GET['category_id'] ?? '') ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="input-container">
            <label for="price">Cena</label>
            <input type="number" step="0.01" id="price" name="price"
                   value="<?= htmlspecialchars($_GET['price'] ?? '') ?>"/>
        </div>

        <div class="btn-container">
            <button class="btn">Edytuj</button>
        </div>
    </form>
</div>
</body>
</html>