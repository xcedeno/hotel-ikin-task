CREATE DATABASE IF NOT EXISTS hotel_ikin;
USE hotel_ikin;

-- Tabla de usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('auxiliar', 'asistente', 'analista', 'jefe', 'admin') NOT NULL,
    department VARCHAR(50) DEFAULT 'Tecnología',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de tareas
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('pendiente', 'en_progreso', 'completada', 'cancelada') DEFAULT 'pendiente',
    priority ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
    assigned_to INT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Insertar usuarios iniciales
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal', 'admin'),
('jefe.tecnologia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', 'jefe'),
('analista1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'xavier cedeño', 'analista'),
('asistente1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos López', 'asistente'),
('asistente2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana Rodríguez', 'asistente'),
('auxiliar1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pedro Martínez', 'auxiliar');

-- Insertar tareas de ejemplo
INSERT INTO tasks (title, description, priority, assigned_to, created_by, due_date) VALUES
('Actualizar sistema de reservas', 'Actualizar a la versión 3.2 del software de reservas', 'alta', 3, 2, '2024-02-15'),
('Reparar impresora recepción', 'La impresora HP LaserJet no imprime', 'media', 5, 3, '2024-02-10'),
('Configurar nuevo access point', 'Instalar AP en área de piscina', 'media', 4, 2, '2024-02-20'),
('Backup base de datos', 'Realizar backup completo de la BD', 'alta', 3, 2, '2024-02-08'),
('Capacitación nuevo software', 'Capacitar personal en nuevo sistema POS', 'baja', 4, 2, '2024-02-25');