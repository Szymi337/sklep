<?php

return function (int $id, string $error, string $description, string $backUrl, string $action) {
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
            .delete-form {
                display: flex;
                flex-direction: column;
                gap: 12px;
                padding: 12px;
            }

            .delete-form-title {
                text-align: center;
            }

            .delete-form-description {
                text-align: center;
            }

            .delete-form-actions {
                display: flex;
                flex-direction: row;
                gap: 12px;
                justify-content: center;
            }
        </style>
    </head>
    <body>
    <div class="container">
        <?php require "../navbar.php"; ?>

        <div class="content">
            <form action="<?= htmlspecialchars($action) ?>" method="POST" class="content delete-form"
                  enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>"/>

                <h1 class="delete-form-title">Potwierdzenie</h1>
                <p class="delete-form-description">
                    <?= htmlspecialchars($description) ?>
                </p>

                <div class="delete-form-actions">
                    <a href="<?= htmlspecialchars($backUrl) ?>" class="btn">Anuluj</a>
                    <button type="submit" class="btn btn-red">Usuń</button>
                </div>
            </form>

            <?php if ($error !== ""): ?>
                <div class="error text-centered"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>
    </div>
    </body>
    </html>

    <?php
};