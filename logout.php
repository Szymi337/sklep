<?php

session_start();

session_destroy();

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    die();
}

unset($_SESSION['user_id']);

header("Location: index.php");
die();
