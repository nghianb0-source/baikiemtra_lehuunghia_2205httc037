<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function isLoggedIn() { return isset($_SESSION['user_id']); }
function getUserRole() { return $_SESSION['role'] ?? ''; }
function checkUser() { if (!isLoggedIn()) redirect('login.php'); }
function checkAdmin() { checkUser(); if (getUserRole() !== 'admin') redirect('index.php'); }
function checkManager() {
    checkUser();
    if (getUserRole() !== 'manager' && getUserRole() !== 'admin') {
        redirect('index.php');
    }
}
function redirect($url) { header("Location: $url"); exit(); }
function hashPassword($pass) { return password_hash($pass, PASSWORD_DEFAULT); }
?>