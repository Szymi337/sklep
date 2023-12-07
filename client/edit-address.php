<?php

function validate_address(string $prefix): ?string
{
    $errors = [];

    if (empty($_POST[$prefix . 'name'])) {
        $errors[] = 'Imię i nazwisko jest wymagane';
    }

    if (empty($_POST[$prefix . 'phone'])) {
        $errors[] = 'Numer telefonu jest wymagany';
    }

    if (empty($_POST[$prefix . 'line'])) {
        $errors[] = 'Adres jest wymagany';
    }

    if (empty($_POST[$prefix . 'zip_code'])) {
        $errors[] = 'Kod pocztowy jest wymagany';
    }

    if (empty($_POST[$prefix . 'city'])) {
        $errors[] = 'Miasto jest wymagane';
    }

    if (!empty($errors)) {
        return implode('<br>', $errors);
    }

    if (!preg_match('/^[0-9]{2}-[0-9]{3}$/', $_POST[$prefix . 'zip_code'])) {
        $errors[] = 'Kod pocztowy jest niepoprawny';
    }

    if (!preg_match('/^[0-9]{9}$/', $_POST[$prefix . 'phone'])) {
        $errors[] = 'Numer telefonu jest niepoprawny';
    }

    if (strlen($_POST[$prefix . 'name']) > 255) {
        $errors[] = 'Imię i nazwisko jest za długie';
    }

    if (strlen($_POST[$prefix . 'phone']) > 255) {
        $errors[] = 'Numer telefonu jest za długi';
    }

    if (strlen($_POST[$prefix . 'line']) > 255) {
        $errors[] = 'Adres jest za długi';
    }

    if (strlen($_POST[$prefix . 'zip_code']) > 255) {
        $errors[] = 'Kod pocztowy jest za długi';
    }

    if (strlen($_POST[$prefix . 'city']) > 255) {
        $errors[] = 'Miasto jest za długie';
    }

    if (!empty($errors)) {
        return implode('<br>', $errors);
    }

    return null;
}

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

if (!isset($_GET['id'])) {
    header("Location: /client/addresses.php");
    die();
}

$id = $_GET['id'];

if (!is_numeric($id)) {
    header("Location: /client/addresses.php");
    die();
}

$stmt = $db->prepare("SELECT * FROM addresses WHERE id = :id AND is_deleted = 0");
$stmt->execute(['id' => $id]);

$address = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$address) {
    header("Location: /client/addresses.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $error = validate_address('');

    if ($error) {
        header("Location: /client/edit-address.php?" . http_build_query($_POST + ['error' => $error]));
        die();
    }

    $stmt = $db->prepare("UPDATE addresses SET name = :name, phone = :phone, line = :line, zip_code = :zip_code, city = :city WHERE id = :id");
    $stmt->execute([
        'id' => $id,
        'name' => $_POST['name'],
        'phone' => $_POST['phone'],
        'line' => $_POST['line'],
        'zip_code' => $_POST['zip_code'],
        'city' => $_POST['city'],
    ]);

    header("Location: /client/addresses.php?" . http_build_query(['success' => 'Adres został dodany.']));
    die();
}

if (empty($_GET['name']) && empty($_GET['phone']) && empty($_GET['line']) && empty($_GET['zip_code']) && empty($_GET['city'])) {
    header("Location: /client/edit-address.php?" . http_build_query($_GET + [
                'name' => $address['name'],
                'phone' => $address['phone'],
                'line' => $address['line'],
                'zip_code' => $address['zip_code'],
                'city' => $address['city'],
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

    <style>
        .create-category-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <?php require "../navbar.php"; ?>

    <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" class="content create-category-form">
        <h2>Edytuj adres</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?= $_GET['error'] ?>
            </div>
        <?php endif; ?>

        <div class="input-container">
            <label for="name">Nazwa</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
        </div>

        <div class="input-container">
            <label for="phone">Telefon</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>">
        </div>

        <div class="input-container">
            <label for="line">Adres</label>
            <input type="text" id="line" name="line" value="<?= htmlspecialchars($_GET['line'] ?? '') ?>">
        </div>

        <div class="input-container">
            <label for="zip_code">Kod pocztowy</label>
            <input type="text" id="zip_code" name="zip_code" value="<?= htmlspecialchars($_GET['zip_code'] ?? '') ?>">
        </div>

        <div class="input-container">
            <label for="city">Miasto</label>
            <input type="text" id="city" name="city" value="<?= htmlspecialchars($_GET['city'] ?? '') ?>">
        </div>

        <div class="btn-container">
            <button type="submit" class="btn">Zaktualizuj</button>
        </div>
    </form>
</div>
</body>
</html>

