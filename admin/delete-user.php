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
    header("Location: /admin/users.php");
    die();
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: /admin/users.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: /admin/users.php");
    die();
}

$renderView = require "delete-view.php";

$renderView(
    id: $user['id'],
    error: $_GET['error'] ?? "",
    description: "Czy na pewno chcesz usunąć użytkownika {$user['login']}?",
    action: "/admin/delete-user.php?" . http_build_query(['id' => $user['id']]),
    backUrl: "/admin/users.php"
);
