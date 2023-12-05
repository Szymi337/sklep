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

$stmt = $db->prepare("SELECT * FROM categories WHERE id = :id AND is_deleted = 0");
$stmt->execute(['id' => $id]);

$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: /admin/categories.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $stmt = $db->prepare("SELECT * FROM products WHERE category_id = :id AND is_deleted = 0");
    $stmt->execute(['id' => $id]);

    if (count($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0) {
        header("Location: /admin/delete-category.php?" . http_build_query([
                'id' => $id, 'error' => 'Nie można usunąć kategorii, która ma przypisane produkty.'
            ]));
        die();
    }

    $stmt = $db->prepare("UPDATE categories SET is_deleted = 1 WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: /admin/categories.php");
    die();
}

$renderView = require "delete-view.php";

$renderView(
    id: $category['id'],
    error: $_GET['error'] ?? "",
    description: "Czy na pewno chcesz usunąć kategorię {$category['name']}?",
    action: "/admin/delete-category.php?" . http_build_query(['id' => $category['id']]),
    backUrl: "/admin/categories.php"
);
