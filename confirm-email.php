<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    die();
}

$token = $_GET['token'] ?? null;
if ($token === null) {
    header("Location: index.php");
    die();
}

$db = require "database.php";

$db->beginTransaction();

$stmt = $db->prepare("SELECT * FROM users WHERE email_confirmation_token = :token");
$stmt->execute(['token' => $token]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: index.php");
    die();
}

$stmt = $db->prepare("UPDATE users SET email_confirmation_token = NULL WHERE id = :id");
$stmt->execute(['id' => $user['id']]);

$db->commit();

header("Location: login.php?" . http_build_query(['success' => "Konto zostało aktywowane, teraz możesz się zalogować"]));