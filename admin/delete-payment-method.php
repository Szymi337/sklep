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

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /admin/payment-methods.php");
    die();
}

$stmt = $db->prepare("SELECT * FROM payment_methods WHERE id = :id AND is_deleted = 0");
$stmt->execute(['id' => $id]);

$method = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$method) {
    header("Location: /admin/payment-methods.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $stmt = $db->prepare("UPDATE payment_methods SET is_deleted = 1 WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: /admin/payment-methods.php?" . http_build_query([
            'success' => 'Metoda płatności została usunięta'
        ]));
    die();
}

$renderView = require "delete-view.php";

$renderView(
    id: $id,
    error: $_GET['error'] ?? '',
    action: $_SERVER['REQUEST_URI'],
    backUrl: "/admin/payment-methods.php",
    description: "Czy na pewno chcesz usunąć metodę płatności {$method['name']}?"
);
