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

$stmt = $db->prepare('SELECT * FROM products WHERE is_deleted = 0');
$stmt->execute();

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$products = array_map(function ($product) {
    $product['price'] = number_format($product['price'], 2, ',', ' ');
    return $product;
}, $products);

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
        .product-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }

        .product-details {
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex-grow: 1;
        }

        .product-card-image {
            width: 100%;
            aspect-ratio: 1/1;

            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
        }

        .product-card-description {
            flex-grow: 1;
        }

        @media (min-width: 600px) {
            .product-card {
                flex-direction: row;
            }
        }

        .no-products {
            text-align: center;
        }

        .product-list {
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <?php require "../navbar.php"; ?>

    <div class="content product-list">
        <?php if (isset($_GET['success'])): ?>
            <div class="success">
                <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>

        <?php foreach ($products as $product): ?>
            <div class="product-card">

                <div class="product-card-image"
                     style="background-image: url('../images/<?= $product['image'] ?>');">
                </div>

                <div class="product-details">
                    <h2 class="product-card-title"><?= $product['name'] ?></h2>

                    <div class="product-card-price">
                        <?= $product['price'] ?> zł
                    </div>

                    <div class="product-card-description">
                        <?= $product['description'] ?>
                    </div>

                    <div class="btn-container">
                        <a class="btn btn-red" href="/admin/delete-product.php?id=<?= $product['id'] ?>">Usuń</a>
                        <a class="btn btn-yellow" href="/admin/edit-product.php?id=<?= $product['id'] ?>">Edytuj</a>
                        <a class="btn" href="/product.php?id=<?= $product['id'] ?>">Szczegóły</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (count($products) === 0): ?>
            <div class="no-products">
                Brak produktów
            </div>
        <?php endif; ?>

        <div class="btn-container">
            <a href="/admin/create-product.php" class="btn">Dodaj</a>
        </div>
    </div>
</div>
</body>
</html>
