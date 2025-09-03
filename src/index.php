<?php
// Redirección al dashboard si ya está logueado, sino al login
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
?>