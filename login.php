<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $oldInput = [];
    if (isset($_POST['login'])) $oldInput['login'] = $_POST['login'];

    if (empty($_POST['login']) || empty($_POST['password'])) {
        $_SESSION['error'] = "Wypełnij wszystkie pola";
        header("Location: /login.php?" . http_build_query($oldInput));
        die();
    }

    if (!is_string($_POST['login']) || strlen($_POST['login']) < 3) {
        $_SESSION['error'] = "Login musi mieć minimum 3 znaki";
        header("Location: /login.php?" . http_build_query($oldInput));
        die();
    }

    if (!is_string($_POST['password']) || strlen($_POST['password']) < 8) {
        $_SESSION['error'] = "Hasło musi mieć minimum 8 znaków";
        header("Location: /login.php?" . http_build_query($oldInput));
        die();
    }

    if (strlen($_POST['password']) > 32) {
        $_SESSION['error'] = "Hasło nie może być dłuższe niż 32 znaki";
        header("Location: /login.php?" . http_build_query($oldInput));
        die();
    }

    if (strlen($_POST['login']) > 32) {
        $_SESSION['error'] = "Login nie może być dłuższy niż 32 znaki";
        header("Location: /login.php?" . http_build_query($oldInput));
        die();
    }

    $login = $_POST['login'];
    $password = $_POST['password'];

    $db = require "database.php";

    $db->beginTransaction();

    $stmt = $db->prepare("SELECT * FROM users WHERE login = :login");
    $stmt->execute(['login' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "Nieprawidłowy login lub hasło";
        header("Location: /login.php?" . http_build_query($oldInput));
        die();
    }

    if ($user['email_confirmation_token'] !== null) {
        header("Location: login.php?" . http_build_query(['error' => "Konto nie zostało aktywowane", ...$oldInput]));
        die();
    }

    if (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Nieprawidłowy login lub hasło";
        header("Location: /login.php?" . http_build_query($oldInput));
        die();
    }

    $db->commit();

    $_SESSION['user_id'] = $user['id'];

    header("Location: index.php");
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

    <style>
        .container > .content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
            max-width: 400px;
            padding: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <?php require "navbar.php" ?>

    <div class="content">
        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST" class="login-card">
            <h2 class="login-card-title">Logowanie</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="error">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="success">
                    <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>

            <div class="input-container">
                <label for="login">Login</label>
                <input type="text" id="login" name="login"
                       value="<?= htmlspecialchars($_GET['login'] ?? '') ?>"/>
            </div>

            <div class="input-container">
                <label for="password">Hasło</label>
                <input type="password" id="password" name="password"/>
            </div>

            <div class="btn-container">
                <a href="forgot-password.php">Przypomnij hasło</a>
                <button class="btn">Zaloguj</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
