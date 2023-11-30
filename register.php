<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $oldInput = [];
    if (isset($_POST['email'])) $oldInput['email'] = $_POST['email'];
    if (isset($_POST['login'])) $oldInput['login'] = $_POST['login'];

    if (empty($_POST['email']) || empty($_POST['login']) || empty($_POST['password']) ||
        empty($_POST['repeat_passsword'])) {
        header("Location: /register.php?" . http_build_query(['error' => 'Wypełnij wszystkie pola.', ...$oldInput]));
        die();
    }

    if (!is_string($_POST['login']) || strlen($_POST['login']) < 3) {
        header("Location: /register.php?" . http_build_query(['error' => 'Login musi mieć minimum 3 znaki.', ...$oldInput]));
        die();
    }

    if (!is_string($_POST['password']) || strlen($_POST['password']) < 8) {
        header("Location: /register.php?" . http_build_query(['error' => 'Hasło musi mieć minimum 8 znaków.', ...$oldInput]));
        die();
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        header("Location: /register.php?" . http_build_query(['error' => 'Nieprawidłowy adres email.', ...$oldInput]));
        die();
    }

    if (!is_string($_POST['repeat_passsword'])) {
        header("Location: /register.php?" . http_build_query(['error' => 'Musisz powtórzyć hasło.', ...$oldInput]));
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

    if (strlen($_POST['password']) > 32) {
        header("Location: /register.php?" . http_build_query(['error' => "Hasło nie może być dłuższe niż 32 znaki.", ...$oldInput]));
        die();
    }

    if (strlen($_POST['login']) > 32) {
        header("Location: /register.php?" . http_build_query(['error' => "Login nie może być dłuższy niż 32 znaki.", ...$oldInput]));
        die();
    }

    if (strlen($_POST['email']) > 255) {
        header("Location: /register.php?" . http_build_query(['error' => "Email nie może być dłuższy niż 255 znaków.", ...$oldInput]));
        die();
    }

    if ($_POST['password'] !== $_POST['repeat_passsword']) {
        header("Location: /register.php?" . http_build_query(['error' => "Hasła nie są takie same.", ...$oldInput]));
        die();
    }

    $email = $_POST['email'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $repeatPassword = $_POST['repeat_passsword'];

    $db = require "database.php";

    $db->beginTransaction();

    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        header("Location: /register.php?" . http_build_query(['error' => "Użytkownik o podanym adresie email już istnieje.", ...$oldInput]));
        die();
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE login = :login");
    $stmt->execute(['login' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        header("Location: /register.php?" . http_build_query(['error' => 'Użytkownik o podanym loginie już istnieje.', ...$oldInput]));
        die();
    }

    $randomizer = new \Random\Randomizer();
    $crockfordAlphabet = '0123456789ABCDEFGHIJKMNPQRSTVWXYZabcdefghijkmnpqrstvwxyz!@#$%^&*()\-_=+{};:,<.>';
    $token = $randomizer->getBytesFromString($crockfordAlphabet, 32);

    $stmt = $db->prepare("INSERT INTO users (email, login, password, email_confirmation_token) VALUES (:email, :login, :password, :token)");
    $stmt->execute([
        'email' => $email,
        'login' => $login,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'token' => $token
    ]);

    $phpmailer = require "mail.php";

    $phpmailer->setFrom('no-reply@sor-tokajuk-sklep.itedya.com');
    $phpmailer->addAddress($email);
    $phpmailer->Subject = "Potwierdź maila";
    $phpmailer->Body = sprintf(
        "Potwierdź maila klikając w ten <a href=\"%s\">link</a>.",
        "http://localhost:8004/confirm-email.php?" . http_build_query(['token' => $token])
    );

    $db->commit();

    $phpmailer->send();

    header("Location: /login.php?" . http_build_query(['success' => "Rejestracja przebiegła pomyślnie, teraz potwierdź maila."]));
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

        .register-card {
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
        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST" class="register-card">
            <h2 class="register-card-title">Rejestracja</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="error">
                    <?= $_GET['error'] ?>
                </div>
            <?php endif; ?>

            <div class="input-container">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_GET['email'] ?? '') ?>"/>
            </div>

            <div class="input-container">
                <label for="login">Login</label>
                <input type="text" id="login" name="login"
                       value="<?= htmlspecialchars($_GET['login'] ?? '') ?>"/>
            </div>

            <div class="input-container">
                <label for="password">Hasło</label>
                <input type="password" id="password" name="password"/>
            </div>

            <div class="input-container">
                <label for="repeat_passsword">Powtórz hasło</label>
                <input type="password" id="repeat_passsword" name="repeat_passsword"/>
            </div>

            <div class="btn-container">
                <button class="btn">Zarejestruj</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
