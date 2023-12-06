<?php

session_start();

$db = require "database.php";

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
            padding: 12px;
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
            max-width: 400px;

            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
        }

        .product-card-description {
            flex-grow: 1;
        }

        @media (min-width: 400px) {
            .product-card {
                flex-direction: row;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <?php require "navbar.php"; ?>

    <div class="content">
        <?php foreach ($products as $product): ?>
            <div class="product-card">

                <div class="product-card-image"
                     style="background-image: url('/images/<?= $product['image'] ?>');">
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
                        <a class="btn btn-green" href="/cart/add.php?id=<?= $product['id'] ?>">Dodaj do koszyka</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>