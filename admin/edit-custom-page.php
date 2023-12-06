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
if (!$id) {
    header("Location: /admin/custom-pages.php");
    die();
}

$stmt = $db->prepare("SELECT * FROM custom_pages WHERE id = :id");
$stmt->execute(['id' => $id]);

$customPage = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customPage) {
    header("Location: /admin/custom-pages.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $oldInput = [
        'id' => $_POST['id'] ?? '',
        'name' => $_POST['name'] ?? '',
        'content' => $_POST['content'] ?? '',
    ];

    if (!is_string($_POST['name'] ?? null)) {
        header("Location: /admin/custom-pages.php?" . http_build_query([
                'error' => 'Pole nazwa jest wymagane.', ...$oldInput
            ]));
        die();
    }

    $name = trim($_POST['name']);

    if (strlen($name) < 3) {
        header("Location: /admin/edit-custom-page.php?" . http_build_query([
                'error' => 'Nazwa musi mieć minimum 3 znaki.', ...$oldInput
            ]));
        die();
    }

    if (strlen($name) > 64) {
        header("Location: /admin/edit-custom-page.php?" . http_build_query([
                'error' => 'Nazwa nie może być dłuższa niż 64 znaki.', ...$oldInput
            ]));
        die();
    }

    if (!is_string($_POST['content'] ?? null)) {
        header("Location: /admin/edit-custom-page.php?" . http_build_query([
                'error' => 'Pole treść jest wymagane.', ...$oldInput
            ]));
        die();
    }

    $content = trim($_POST['content']);

    if (strlen($content) < 3) {
        header("Location: /admin/edit-custom-page.php?" . http_build_query([
                'error' => 'Treść musi mieć minimum 3 znaki.', ...$oldInput
            ]));
        die();
    }

    if (strlen($content) > 65535) {
        header("Location: /admin/edit-custom-page.php?" . http_build_query([
                'error' => 'Treść nie może być dłuższa niż 65535 znaków.', ...$oldInput
            ]));
        die();
    }

    $db->beginTransaction();

    $stmt = $db->prepare("SELECT * FROM custom_pages WHERE name = :name AND id != :id");
    $stmt->execute(['name' => $name, 'id' => $id]);

    $customPage = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customPage) {
        header("Location: /admin/edit-custom-page.php?" . http_build_query([
                'id' => $id, 'error' => 'Strona o takiej nazwie już istnieje.', 'name' => $_POST['name']
            ]));
        die();
    }

    $stmt = $db->prepare("UPDATE custom_pages SET name = :name, content = :content WHERE id = :id");
    $stmt->execute(['name' => $name, 'content' => $content, 'id' => $id]);

    $db->commit();

    header("Location: /admin/custom-pages.php?" . http_build_query([
            'success' => 'Strona została zaktualizowana.'
        ]));
    return;
}

if (empty($_GET['name']) && empty($_GET['content'])) {
    header("Location: /admin/edit-custom-page.php?" . http_build_query([
            'id' => $id, 'name' => $customPage['name'], 'content' => $customPage['content']
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

    <form action="/admin/edit-custom-page.php" method="POST" class="content form"
          enctype="multipart/form-data">
        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <input type="hidden" name="id" value="<?= $id ?>"/>

        <div class="input-container">
            <label for="name">Nazwa</label>
            <input type="text" id="name" name="name"
                   value="<?= htmlspecialchars($_GET['name'] ?? '') ?>"/>
        </div>

        <div class="input-container">
            <label for="content">Treść</label>
            <textarea id="content" name="content" cols="30" rows="10"><?= htmlspecialchars($_GET['content'] ?? '') ?></textarea>
        </div>

        <div class="btn-container">
            <button class="btn">Edytuj</button>
        </div>
    </form>
</div>
</body>
</html>