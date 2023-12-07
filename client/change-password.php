<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    die();
}

$db = require "../database.php";

$stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(['id' => $_SESSION['user_id']]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: /login.php');
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (!isset($_POST['old_password']) || !isset($_POST['new_password']) || !isset($_POST['confirm_new_password'])) {
        header('Location: /client/change-password.php?' . http_build_query(['error' => 'Wypełnij wszystkie pola']));
        die();
    }

    if ($_POST['new_password'] !== $_POST['confirm_new_password']) {
        header('Location: /client/change-password.php?' . http_build_query(['error' => 'Hasła nie są takie same']));
        die();
    }

    if (!password_verify($_POST['old_password'], $user['password'])) {
        header('Location: /client/change-password.php?' . http_build_query(['error' => 'Stare hasło jest nieprawidłowe']));
        die();
    }

    if (!preg_match('/[A-Z]/', $_POST['new_password'])) {
        header("Location: /register.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jedną wielką literę.']));
        die();
    }

    if (!preg_match('/[a-z]/', $_POST['new_password'])) {
        header("Location: /register.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jedną małą literę.']));
        die();
    }

    if (!preg_match('/[0-9]/', $_POST['new_password'])) {
        header("Location: /register.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jedną cyfrę.']));
        die();
    }

    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $_POST['new_password'])) {
        header("Location: /register.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jeden znak specjalny.']));
        die();
    }

    if (strlen($_POST['new_password']) > 32) {
        header("Location: /register.php?" . http_build_query(['error' => "Hasło nie może być dłuższe niż 32 znaki."]));
        die();
    }

    $stmt = $db->prepare('UPDATE users SET password = :password WHERE id = :id');
    $stmt->execute(['password' => password_hash($_POST['new_password'], PASSWORD_DEFAULT), 'id' => $_SESSION['user_id']]);

    header('Location: /client/change-password.php?' . http_build_query(['success' => 'Zmieniono hasło']));
    die();
}

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
</head>
<body>
<div class="container">
    <?php require "../navbar.php"; ?>

    <form method="POST" action="/client/change-password.php" class="content form">
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>

        <div class="input-container">
            <label for="old_password">Stare hasło</label>
            <input type="password" name="old_password" id="old_password"/>
        </div>

        <div class="input-container">
            <label for="new_password">Nowe hasło</label>
            <input type="password" name="new_password" id="new_password"/>
        </div>

        <div class="input-container">
            <label for="confirm_new_password">Powtórz nowe hasło</label>
            <input type="password" name="confirm_new_password" id="confirm_new_password"/>
        </div>

        <div class="btn-container">
            <button class="btn" id="change-data">Zmień dane</button>
        </div>
    </form>
</div>
</body>
</html>