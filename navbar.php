<?php
$db = require __DIR__ . '/database.php';
$stmt = $db->prepare('SELECT * FROM custom_pages');
$stmt->execute();

$customPages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="navbar">
    <a class="navbar-brand" href="/">Hiperventilation</a>

    <div class="navbar-items" navbar-items>
        <a href="/index.php" class="navbar-item">Strona główna</a>
        <a href="/cart/index.php" class="navbar-item">Koszyk</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="/login.php" class="navbar-item">Logowanie</a>
            <a href="/register.php" class="navbar-item">Rejestracja</a>
        <?php else: ?>
            <div class="dropdown">
                <div class="navbar-item">Konto</div>
                <div class="dropdown-items">
                    <a href="/client/change-data.php" class="navbar-item">Zmiana danych</a>
                    <a href="/client/change-password.php" class="navbar-item">Zmiana hasła</a>
                    <a href="/client/orders.php" class="navbar-item">Zamówienia</a>
                    <a href="/client/addresses.php" class="navbar-item">Adresy</a>
                </div>
            </div>

            <div class="dropdown">
                <div class="navbar-item">Zarządzanie</div>
                <div class="dropdown-items">
                    <a href="/admin/products.php" class="navbar-item">Zarządzanie produktami</a>
                    <a href="/admin/categories.php" class="navbar-item">Zarządzanie kategoriami</a>
                    <a href="/admin/payment-methods.php" class="navbar-item">Zarządzanie sposobami płatności</a>
                    <a href="/admin/delivery-methods.php" class="navbar-item">Zarządzanie sposobami wysyłki</a>
                    <a href="/admin/custom-pages.php" class="navbar-item">Zarządzanie stronami</a>
                    <a href="/admin/users.php" class="navbar-item">Zarządzanie użytkownikami</a>
                </div>
            </div>
            <a href="/logout.php" class="navbar-item">Wyloguj</a>
        <?php endif; ?>

        <?php foreach ($customPages as $page): ?>
            <a href="/custom-page.php?<?= http_build_query(['id' => $page['id']]) ?>"
               class="navbar-item"><?= htmlspecialchars($page['name']) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="navbar-trigger" navbar-trigger>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
             stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/>
        </svg>
    </div>
</div>

<script src="/js/navbar.js"></script>
