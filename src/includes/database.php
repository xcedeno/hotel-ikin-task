<?php
try {
    $host = 'mysql';
    $dbname = 'hotel_ikin';
    $username = 'hotel_user';
    $password = 'userpassword';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión con la base de datos. Por favor, intente más tarde.");
}
?>