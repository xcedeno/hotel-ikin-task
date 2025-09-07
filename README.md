# 🏨 Sistema de Gestión de Tareas y Tickets - Hotel Ikin

## 🛠️ Tecnologías y Herramientas
![Php](https://img.shields.io/badge/PHP-8.2%252B-777BB4?style=for-the-badge&logo=php)

![MySql](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)

![BootStrap](https://img.shields.io/badge/Bootstrap-5.1-7952B3?style=for-the-badge&logo=bootstrap)

![Docker](https://img.shields.io/badge/Docker-%E2%9C%93-2496ED?style=for-the-badge&logo=docker)


    Sistema completo de gestión interna para el departamento de tecnología del Hotel Ikin. 
    Incluye módulos de tareas, tickets de soporte, gestión de usuarios y reportes, con permisos diferenciados por roles.

### ✨ Características Principales

### 🔐 Sistema de Autenticación y Roles

        5 Roles Jerárquicos: Admin, Jefe, Analista, Asistente, Auxiliar
        Permisos Granulares: Cada rol tiene capacidades específicas
        Login Seguro: Con validación y protección de contraseñas

### ✅ Módulo de Gestión de Tareas

        Creación, edición y eliminación de tareas
        Sistema de prioridades (Baja, Media, Alta, Urgente)
        Estados de progreso (Pendiente, En Progreso, Completada, Cancelada)
        Asignación a usuarios específicos
        Fechas de vencimiento y seguimiento

### 🎫 Sistema de Tickets de Soporte

        Numeración automática de tickets (TKT-2024-0001)
        Departamento de destino (Sistemas, Aplicaciones, Infraestructura, etc.)
        Chat integrado entre usuarios y técnicos
        Seguimiento de tiempo y resolución
        Notificaciones de vencimiento

### 👥 Gestión de Usuarios
        CRUD completo de usuarios
        Asignación de roles y departamentos         
        Eliminación segura con verificación de dependencias
        Perfiles de usuario editables

### 📊 Reportes y Estadísticas
        Dashboard con métricas clave
        Estadísticas por departamento
        Tiempos promedio de resolución
        Eficiencia por usuario/departamento

### 🛠️ Tecnologías Utilizadas
        Tecnología	Versión	Propósito
        PHP	8.2+	Backend con POO
        MySQL	8.0	Base de datos
        Bootstrap	5.1	Frontend y UI
        Docker	Latest	Contenedores
        Apache	2.4	Servidor web

### 🏗️ Estructura del Proyecto

<img width="611" height="687" alt="image" src="https://github.com/user-attachments/assets/ca35f014-4f1c-4a10-a95f-c1a233ae2cc0" />


### 👥 Roles y Permisos

#### Administrador (Admin)

    ✅ Acceso completo al sistema

    ✅ Gestión de todos los usuarios

    ✅ Ver/editar/eliminar cualquier contenido

    ✅ Configuración del sistema

#### Jefe de Tecnología
    ✅ Gestión de tickets y tareas

    ✅ Reportes y estadísticas

    ✅ Supervisión del personal

    ✅ Acceso casi completo (excepto configuraciones críticas)

#### Analista
    ✅ Ver todos los tickets

    ✅ Editar cualquier ticket

    ✅ Asignar tickets a otros usuarios

    ✅ Crear tareas internas

#### Asistente Técnico
    ✅ Crear y editar tickets

    ✅ Ver tickets asignados y públicos

    ✅ Comunicarse via chat

    ✅ Gestión de tareas propias

#### Auxiliar Técnico
    ✅ Ver solo tickets asignados

    ✅ Comunicarse via chat en tickets asignados

    ✅ Actualizar estados de tickets

#### Usuario Final
    ✅ Crear nuevos tickets

    ✅ Ver sus propios tickets

    ✅ Comunicarse via chat en sus tickets

    ✅ Editar sus tickets no asignados

## 🚀 Instalación Rápida
### Prerrequisitos

#### Verificar instalaciones
    docker --version
    docker-compose --version
    git --version

### 1. Clonar Repositorio
    git clone https://github.com/xcedeno/hotel-ikin-task.git
    cd hotel-ikin-task

### 2. Ejecutar con Docker

  #### Iniciar contenedores
    docker-compose up -d

  #### Ver estado
    docker-compose ps

### Ver logs en tiempo real
    docker-compose logs -f php
#### 3. Acceder al Sistema
    🌐 URL: http://localhost:8080

## 🔐 Credenciales por Defecto:

#### Administrador
    Usuario: admin
    Contraseña: admin

#### 5. Verificar Instalación

#### Verificar que todo funcione
    curl -I http://localhost:8080

#### Ver base de datos
    docker-compose exec mysql mysql -u root -p hotel_ikin
## 📋 Funcionalidades por Módulo
### Dashboard Principal
#### Estadísticas en tiempo real
    - 📊 Total de tareas y tickets
    - ⚡ Tareas pendientes y urgentes
    - ✅ Tareas completadas
    - 📈 Gráficos de productividad
### Gestión de Tareas

#### Características principales
    - ➕ Creación con título, descripción, prioridad
    - 👥 Asignación a usuarios específicos
    - 📅 Fechas de vencimiento y recordatorios
    - 🔍 Filtros por estado, prioridad, asignado
    - 📄 Paginación y sistema de búsqueda
    - 📊 Seguimiento de progreso visual
### Sistema de Tickets

#### Flujo de trabajo
    1. 🎫 Creación con numeración automática: TKT-AÑO-SECUENCIA
    2. 🏢 Asignación a departamento especializado
    3. 💬 Chat integrado en tiempo real
    4. 📋 Historial completo de cambios
    5. ⏰ Notificaciones de vencimiento
    6. ✅ Cierre y documentación de solución

### Ejemplo de Código: Creación de Ticket

    <?php
    // Generación automática de número de ticket
    function generateTicketNumber($pdo) {
    $year = date('Y');
    $lastTicket = $pdo->query("SELECT ticket_number FROM support_tickets...");
    return "TKT-$year-".str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
    ?>
### 🎨 Interfaz de Usuario
#### Design System

   <img width="516" height="356" alt="image" src="https://github.com/user-attachments/assets/98d36fb3-be9d-4af1-ad00-dd7a08aaf530" />


### Componentes Personalizados
    - Cards interactivas con efectos hover
    - Badges de prioridad con colores semánticos
    - Barras de progreso animadas
    - Modales de confirmación elegantes
    - Tablas responsivas con paginación

### 🔒 Seguridad Implementada
    Característica	Implementación
    SQL Injection	Prepared Statements PDO
    XSS Protection	htmlspecialchars() output
    CSRF Protection	Tokens de verificación
    Session Security	Sesiones PHP seguras
    Password Hashing	password_hash() BCrypt
    Input Validation	Validación server-side
    
### Ejemplo de Seguridad

   <img width="514" height="274" alt="image" src="https://github.com/user-attachments/assets/cb95d81a-d754-4b0a-b766-c625659ebe26" />




### 📊 Base de Datos

   <img width="682" height="608" alt="image" src="https://github.com/user-attachments/assets/84510599-e283-4303-8691-21c40d49a553" />



    
## 🐛 Troubleshooting
#### Problemas Comunes y Soluciones

### Error de conexión a MySQL
    docker-compose restart mysql

#### Permisos de archivos
    chmod -R 755 src/

#### Puerto ocupado
    Cambiar puerto en docker-compose.yml
    ports:
    - "8081:80"

#### Reconstruir contenedores
    docker-compose down
    docker-compose up -d --build

## Comandos Útiles
    
#### Ver logs en tiempo real
    docker-compose logs -f

#### Acceder a la base de datos
    docker-compose exec mysql mysql -u root -p hotel_ikin

#### Backup de base de datos
    docker-compose exec mysql mysqldump -u root -p hotel_ikin > backup.sql

#### Restore de base de datos
    docker-compose exec -i mysql mysql -u root -p hotel_ikin < backup.sql
## 📈 Métricas del Proyecto
    Métrica	Valor
        Líneas de código PHP	~5,000+
        Archivos PHP	50+
        Tablas de BD	8 principales
        Tiempo desarrollo	2+ semanas
## 🚀 Roadmap Futuro
    Notificaciones por Email 📧

    API REST para integraciones 🔌

    App Móvil nativa 📱

    Sistema de Documentación interna 📚

    Integración con Monitoreo 📊

    Dashboard Tiempo Real ⚡

    Sistema de Backup automático 💾




📞 Soporte y Contacto
📧 Email: XXXXXX@hotelikin.com

🐛 Issues: GitHub Issues

💬 Discord: Canal de Desarrollo

📄 Licencia
Este proyecto está bajo la Licencia MIT. Ver el archivo LICENSE para más detalles.

text
MIT License
Copyright (c) 2024 Hotel Ikin





