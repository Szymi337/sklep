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
    if (empty($_POST['login']) || empty($_POST['email'])) {
        header('Location: /client/change-data.php?' . http_build_query($_POST + [
                    'error' => 'Wypełnij wszystkie pola'
                ]));
        die();
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        header('Location: /client/change-data.php?' . http_build_query($_POST + [
                    'error' => 'Niepoprawny adres email'
                ]));
        die();
    }

    if (strlen($_POST['login']) < 3) {
        header('Location: /client/change-data.php?' . http_build_query($_POST + [
                    'error' => 'Login musi mieć co najmniej 3 znaki'
                ]));
        die();
    }

    if (strlen($_POST['login']) > 32) {
        header('Location: /client/change-data.php?' . http_build_query($_POST + [
                    'error' => 'Login może mieć maksymalnie 32 znaki'
                ]));
        die();
    }

    if (strlen($_POST['email']) > 64) {
        header('Location: /client/change-data.php?' . http_build_query($_POST + [
                    'error' => 'Email może mieć maksymalnie 64 znaki'
                ]));
        die();
    }

    $stmt = $db->prepare('SELECT * FROM users WHERE login = :login AND id != :id');
    $stmt->execute([
        'login' => $_POST['login'],
        'id' => $_SESSION['user_id']
    ]);

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        header('Location: /client/change-data.php?' . http_build_query($_POST + [
                    'error' => 'Login jest już zajęty'
                ]));
        die();
    }

    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email AND id != :id');
    $stmt->execute([
        'email' => $_POST['email'],
        'id' => $_SESSION['user_id']
    ]);

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        header('Location: /client/change-data.php?' . http_build_query($_POST + ['error' => 'Email jest już zajęty']));
        die();
    }

    $stmt = $db->prepare('UPDATE users SET login = :login, email = :email WHERE id = :id');
    $stmt->execute([
        'login' => $_POST['login'],
        'email' => $_POST['email'],
        'id' => $_SESSION['user_id']
    ]);

    header('Location: /client/change-data.php?' . http_build_query($_POST + ['success' => 'Zmieniono dane']));
    die();
}

if (empty($_GET['login']) && empty($_GET['email'])) {
    header('Location: /client/change-data.php?' . http_build_query([
            'login' => $user['login'],
            'email' => $user['email']
        ]));
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

    <form method="POST" action="/client/change-data.php" class="content form">
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>

        <div class="input-container">
            <label for="login">Login</label>
            <input type="text" id="login" name="login" value="<?= htmlspecialchars($_GET['login'] ?? '') ?>">
        </div>

        <div class="input-container">
            <label for="email">Email</label>
            <input type="text" id="email" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
        </div>

        <div class="btn-container">
            <button class="btn btn-primary" id="change-data">Zmień dane</button>
        </div>
    </form>
</div>
</body>
</html>