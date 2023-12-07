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

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $oldInput = [
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
    ];

    if (!is_string($_POST['username'] ?? null)) {
        header("Location: /admin/create-custom-page.php?" . http_build_query([
                'error' => 'Uzupełnij wszystkie pola.', ...$oldInput
            ]));
        die();
    }

    $username = trim($_POST['username']);

    if (strlen($username) < 3) {
        header("Location: /admin/create-custom-page.php?" . http_build_query([
                'error' => 'Nazwa musi mieć minimum 3 znaki.', ...$oldInput
            ]));
        die();
    }

    if (strlen($username) > 64) {
        header("Location: /admin/create-custom-page.php?" . http_build_query([
                'error' => 'Nazwa nie może być dłuższa niż 64 znaki.', ...$oldInput
            ]));
        die();
    }

    if (!is_string($_POST['email'] ?? null)) {
        header("Location: /admin/create-user.php?" . http_build_query([
                'error' => 'Uzupełnij wszystkie pola.', ...$oldInput
            ]));
        die();
    }

    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /admin/create-user.php?" . http_build_query([
                'error' => 'Podaj poprawny adres email.', ...$oldInput
            ]));
        die();
    }

    if (strlen($email) > 255) {
        header("Location: /admin/create-user.php?" . http_build_query([
                'error' => 'Email nie może być dłuższy niż 255 znaków.', ...$oldInput
            ]));
        die();
    }

    if (!is_string($_POST['password']) || strlen($_POST['password']) < 8) {
        header("Location: /admin/create-user.php?" . http_build_query(['error' => 'Hasło musi mieć minimum 8 znaków.', ...$oldInput]));
        die();
    }

    if (!preg_match('/[A-Z]/', $_POST['password'])) {
        header("Location: /admin/create-user.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jedną wielką literę.', ...$oldInput]));
        die();
    }

    if (!preg_match('/[a-z]/', $_POST['password'])) {
        header("Location: /admin/create-user.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jedną małą literę.', ...$oldInput]));
        die();
    }

    if (!preg_match('/[0-9]/', $_POST['password'])) {
        header("Location: /admin/create-user.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jedną cyfrę.', ...$oldInput]));
        die();
    }

    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $_POST['password'])) {
        header("Location: /admin/create-user.php?" . http_build_query(['error' => 'Hasło musi zawierać przynajmniej jeden znak specjalny.', ...$oldInput]));
        die();
    }

    if (strlen($_POST['password']) > 32) {
        header("Location: /admin/create-user.php?" . http_build_query(['error' => "Hasło nie może być dłuższe niż 32 znaki.", ...$oldInput]));
        die();
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE login = :login");
    $stmt->execute(['login' => $username]);

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        header("Location: /admin/create-user.php?" . http_build_query([
                'error' => 'Użytkownik o takiej nazwie już istnieje.', ...$oldInput
            ]));
        die();
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        header("Location: /admin/create-user.php?" . http_build_query([
                'error' => 'Użytkownik o takim adresie email już istnieje.', ...$oldInput
            ]));
        die();
    }

    $stmt = $db->prepare("INSERT INTO users (login, email, password, is_admin) VALUES (:login, :email, :password, :is_admin)");
    $stmt->execute([
        'login' => $username,
        'email' => $email,
        'password' => password_hash(trim($_POST['password']), PASSWORD_BCRYPT),
        'is_admin' => 1
    ]);

    header("Location: /admin/users.php?" . http_build_query([
            'success' => 'Użytkownik został dodany.'
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

    <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" class="content form">
        <h2>Dodaj pracownika</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?= $_GET['error'] ?>
            </div>
        <?php endif; ?>

        <div class="input-container">
            <label for="username">Nazwa użytkownika</label>
            <input type="text" name="username" id="username" value="<?= htmlspecialchars($_GET['username'] ?? '') ?>">
        </div>

        <div class="input-container">
            <label for="email">Email</label>
            <input type="text" name="email" id="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
        </div>

        <div class="input-container">
            <label for="password">Hasło</label>
            <input type="password" name="password" id="password">
        </div>

        <div class="btn-container">
            <button class="btn">Dodaj</button>
        </div>
    </form>
</div>
</body>
</html>

