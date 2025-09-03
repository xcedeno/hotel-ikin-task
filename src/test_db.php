<?php
$host = 'mysql';
$dbname = 'app_db';
$username = 'app_user';
$password = 'userpassword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "✅ Conexión a MySQL exitosa!<br>";
    
    // Crear tabla de prueba
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        email VARCHAR(50) NOT NULL
    )");
    
    // Insertar datos de prueba
    $pdo->exec("INSERT INTO test_users (name, email) VALUES 
        ('xavier cedeño', 'juan@example.com'),
        ('María García', 'maria@example.com')");
    
    echo "✅ Tabla y datos de prueba creados<br>";
    
    // Mostrar datos
    $stmt = $pdo->query("SELECT * FROM test_users");
    echo "<h3>Usuarios en la base de datos:</h3>";
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']}, Nombre: {$row['name']}, Email: {$row['email']}<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}
?>