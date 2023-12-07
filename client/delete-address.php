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

$id = ($_SERVER['REQUEST_METHOD'] === "POST") ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null);
if (!is_numeric($id)) {
    header("Location: /client/addresses.php");
    die();
}

$stmt = $db->prepare("SELECT * FROM addresses WHERE id = :id AND is_deleted = 0");
$stmt->execute(['id' => $id]);

$address = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$address) {
    header("Location: /client/addresses.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $stmt = $db->prepare("UPDATE addresses SET is_deleted = 1 WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: /client/addresses.php");
    die();
}

$renderView = require "../admin/delete-view.php";

$renderView(
    id: $address['id'],
    error: $_GET['error'] ?? "",
    description: "Czy na pewno chcesz usunąć ten adres?",
    action: "/client/delete-address.php?" . http_build_query(['id' => $address['id']]),
    backUrl: "/client/addresses.php"
);
