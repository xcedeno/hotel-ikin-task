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

ALTER DATABASE hotel_ikin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tasks CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Asegurar que las tablas usan UTF-8
ALTER TABLE users MODIFY username VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8

-- Tabla de departamentos/sectores
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de tickets de soporte
CREATE TABLE support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    department_id INT NOT NULL,
    client_name VARCHAR(100) NOT NULL,
    client_email VARCHAR(100) NOT NULL,
    client_phone VARCHAR(20),
    priority ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
    status ENUM('abierto', 'en_proceso', 'esperando_cliente', 'resuelto', 'cerrado', 'cancelado') DEFAULT 'abierto',
    assigned_to INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    due_date DATE,
    resolution_description TEXT,
    closed_at TIMESTAMP NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Insertar departamentos predeterminados
INSERT INTO departments (name, description) VALUES
('Sistemas', 'Problemas con hardware, software y redes'),
('Aplicaciones', 'Soporte para aplicaciones internas'),
('Infraestructura', 'Servidores, redes y telecomunicaciones'),
('Base de Datos', 'Problemas con bases de datos'),
('Soporte General', 'Problemas generales de TI');

-- Insertar algunos tickets de ejemplo
INSERT INTO support_tickets (ticket_number, title, description, department_id, client_name, client_email, client_phone, priority, status) VALUES
('TKT-2024-0001', 'PC no enciende', 'La computadora de recepción no enciende, no emite sonidos ni luces', 1, 'María González', 'maria@hotelikin.com', '555-1234', 'alta', 'abierto'),
('TKT-2024-0002', 'Error en sistema de reservas', 'No se pueden procesar nuevas reservas, error 500 al guardar', 2, 'Carlos López', 'carlos@hotelikin.com', '555-5678', 'urgente', 'en_proceso'),
('TKT-2024-0003', 'Internet lento en habitaciones', 'Los huéspedes se quejan de internet lento en las habitaciones del ala norte', 3, 'Ana Rodríguez', 'ana@hotelikin.com', '555-9012', 'media', 'abierto');