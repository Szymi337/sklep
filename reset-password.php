<?php

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

$stmt = $db->prepare("SELECT * FROM users WHERE password_reset_token = :token");
$stmt->execute(['token' => $token]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: index.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $oldInput = [
        'password' => $_POST['password'] ?? '',
        'repeat_password' => $_POST['repeat_password'] ?? '',
        'token' => $token
    ];


    if (!isset($oldInput['password'])) {
        header("Location: reset-password.php?" . http_build_query(['error' => "Nie podano hasła", ...$oldInput]));
        die();
    }

    if (!is_string($oldInput['password'])) {
        header("Location: reset-password.php?" . http_build_query(['error' => "Niepoprawne hasło", ...$oldInput]));
        die();
    }

    if (!isset($oldInput['repeat_password'])) {
        header("Location: reset-password.php?" . http_build_query(['error' => "Nie podano powtórzenia hasła", ...$oldInput]));
        die();
    }

    if (!is_string($oldInput['repeat_password'])) {
        header("Location: reset-password.php?" . http_build_query(['error' => "Niepoprawne powtórzenie hasła", ...$oldInput]));
        die();
    }

    if (strlen($oldInput['password']) < 8) {
        header("Location: reset-password.php?" . http_build_query(['error' => "Hasło musi mieć minimum 8 znaków", ...$oldInput]));
        die();
    }

    if (strlen($oldInput['password']) > 32) {
        header("Location: reset-password.php?" . http_build_query(['error' => "Hasło nie może być dłuższe niż 32 znaki", ...$oldInput]));
        die();
    }

    if (!preg_match('/[A-Z]/', $_POST['password'])) {
        header("Location: /register.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jedną wielką literę.', ...$oldInput]));
        die();
    }

    if (!preg_match('/[a-z]/', $_POST['password'])) {
        header("Location: /register.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jedną małą literę.', ...$oldInput]));
        die();
    }

    if (!preg_match('/[0-9]/', $_POST['password'])) {
        header("Location: /register.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jedną cyfrę.', ...$oldInput]));
        die();
    }

    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $_POST['password'])) {
        header("Location: /register.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jeden znak specjalny.', ...$oldInput]));
        die();
    }

    if ($oldInput['password'] !== $oldInput['repeat_password']) {
        header("Location: reset-password.php?" . http_build_query(['error' => "Hasła nie są takie same", ...$oldInput]));
        die();
    }

    $stmt = $db->prepare("UPDATE users SET password = :password, password_reset_token = NULL WHERE id = :id");
    $stmt->execute(['password' => password_hash($oldInput['password'], PASSWORD_DEFAULT), 'id' => $user['id']]);

    $db->commit();

    header("Location: login.php?" . http_build_query(['success' => "Hasło zostało zmienione, teraz możesz się zalogować"]));
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

        .reset-password-card {
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
        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST" class="reset-password-card">
            <h2 class="login-card-title">Zresetuj hasło</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="error">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <div class="input-container">
                <label for="password">Nowe hasło</label>
                <input type="password" id="password" name="password"/>
            </div>

            <div class="input-container">
                <label for="repeat_password">Powtórz hasło</label>
                <input type="password" id="repeat_password" name="repeat_password"/>
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

