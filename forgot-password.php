<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $oldInput = ['email' => $_POST['email'] ?? ''];

    if (!isset($oldInput['email'])) {
        header("Location: forgot-password.php?" . http_build_query(['error' => "Nie podano adresu email", ...$oldInput]));
        die();
    }

    if (!is_string($oldInput['email'])) {
        header("Location: forgot-password.php?" . http_build_query(['error' => "Niepoprawny adres email", ...$oldInput]));
        die();
    }

    $db = require_once "database.php";

    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $oldInput['email']]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: forgot-password.php?" . http_build_query(['error' => "Nie znaleziono użytkownika", ...$oldInput]));
        die();
    }

    if ($user['email_confirmation_token'] !== null) {
        header("Location: forgot-password.php?" . http_build_query(['error' => "Konto nie zostało aktywowane", ...$oldInput]));
        die();
    }

    $randomizer = new \Random\Randomizer();
    $token = $randomizer->getBytesFromString('0123456789ABCDEFGHIJKMNPQRSTVWXYZabcdefghijkmnpqrstvwxyz!@#$%^&*()\-_=+{};:,<.>', 32);

    $stmt = $db->prepare("UPDATE users SET password_reset_token = :token WHERE id = :id");
    $stmt->execute(['token' => $token, 'id' => $user['id']]);

    $phpmailer = require "mail.php";

    $phpmailer->setFrom('no-reply@sor-tokajuk-sklep.itedya.com');
    $phpmailer->addAddress($user['email']);
    $phpmailer->Subject = "Przypomnij hasło";
    $phpmailer->Body = sprintf(
        "Zresetuj hasło klikając w ten <a href=\"%s\">link</a>.",
        "http://localhost:8004/reset-password.php?" . http_build_query(['token' => $token])
    );

    $phpmailer->send();

    header("Location: forgot-password.php?" . http_build_query(['success' => "Na podany adres email został wysłany link do resetu hasła"]));
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

        .forgot-password-card {
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
        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST" class="forgot-password-card">
            <h2 class="forgot-password-card-title">Resetowanie hasła</h2>

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
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>"/>
            </div>

            <div class="btn-container">
                <button class="btn">Zresetuj hasło</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
