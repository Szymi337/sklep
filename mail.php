<?php

require_once "./PHPMailer/PHPMailer.php";
require_once "./PHPMailer/SMTP.php";
require_once "./PHPMailer/Exception.php";

$phpmailer = new PHPMailer\PHPMailer\PHPMailer();

$phpmailer->isSMTP();
$phpmailer->Host = 'sandbox.smtp.mailtrap.io';
$phpmailer->SMTPAuth = true;
$phpmailer->Port = 2525;
$phpmailer->Username = '04e5ad89a18eb3';
$phpmailer->Password = '17207a3bf4b8bb';

$phpmailer->isHTML();
$phpmailer->CharSet = 'UTF-8';

return $phpmailer;