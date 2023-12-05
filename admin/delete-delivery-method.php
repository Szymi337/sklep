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
if (!is_numeric($id)) {
    header("Location: /admin/categories.php");
    die();
}

$stmt = $db->prepare("SELECT * FROM delivery_methods WHERE id = :id AND is_deleted = 0");
$stmt->execute(['id' => $id]);

$deliveryMethod = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deliveryMethod) {
    header("Location: /admin/delivery-methods.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $stmt = $db->prepare("UPDATE delivery_methods SET is_deleted = 1 WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: /admin/delivery-methods.php?" . http_build_query([
            'success' => 'Pomyślnie usunięto metodę wysyłki.'
        ]));
    die();
}

$renderView = require "delete-view.php";

$renderView(
    id: $deliveryMethod['id'],
    error: $_GET['error'] ?? "",
    description: "Czy na pewno chcesz usunąć metodę dostawy {$deliveryMethod['name']}?",
    action: "/admin/delete-delivery-method.php?" . http_build_query(['id' => $deliveryMethod['id']]),
    backUrl: "/admin/delivery-methods.php"
);
