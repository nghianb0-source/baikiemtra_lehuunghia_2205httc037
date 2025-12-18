<?php
$pdo = new PDO("mysql:host=localhost;dbname=mini_ecommerce;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>