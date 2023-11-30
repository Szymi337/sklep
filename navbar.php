<div class="navbar">
    <h1 class="navbar-brand">Hiperventilation</h1>

    <div class="navbar-items" navbar-items>
        <a href="/index.php" class="navbar-item">Strona główna</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="/login.php" class="navbar-item">Logowanie</a>
            <a href="/register.php" class="navbar-item">Rejestracja</a>
        <?php else: ?>
            <a href="/logout.php" class="navbar-item">Wyloguj</a>
        <?php endif; ?>
    </div>

    <div class="navbar-trigger" navbar-trigger>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
             stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/>
        </svg>
    </div>
</div>

<script src="/js/navbar.js"></script>
