<?php

session_start();

$id = $_GET['id'] ?? null;
if (!is_numeric($id)) {
    header("Location: /");
    die();
}

$db = require "../database.php";

$stmt = $db->prepare("SELECT * FROM products WHERE id = :id AND is_deleted = 0");
$stmt->execute(['id' => $id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: /");
    die();
}

$cart = $_SESSION['cart'] ?? [];

if (isset($cart[$id])) {
    $cart[$id]--;

    if ($cart[$id] <= 0) {
        unset($cart[$id]);
    }
}

$_SESSION['cart'] = $cart;


$backUrl = $_GET['back_url'] ?? "/";
header("Location: " . $backUrl);