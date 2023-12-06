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
    header("Location: /admin/custom-pages.php");
    die();
}

$stmt = $db->prepare("SELECT * FROM custom_pages WHERE id = :id");
$stmt->execute(['id' => $id]);

$customPage = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customPage) {
    header("Location: /admin/custom-pages.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $stmt = $db->prepare("DELETE FROM custom_pages WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: /admin/custom-pages.php?" . http_build_query([
            'success' => 'Pomyślnie usunięto stronę.'
        ]));
    die();
}

$renderView = require "delete-view.php";

$renderView(
    id: $customPage['id'],
    error: $_GET['error'] ?? "",
    description: "Czy na pewno chcesz usunąć stronę {$customPage['name']}?",
    action: "/admin/delete-custom-page.php?" . http_build_query(['id' => $customPage['id']]),
    backUrl: "/admin/custom-pages.php"
);
