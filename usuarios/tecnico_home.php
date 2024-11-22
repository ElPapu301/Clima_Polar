<?php
session_start(); // Asegura que la sesión esté iniciada

include("../bd_conexion.php");

// Redirigir si el usuario no ha iniciado sesión o no es Técnico
if (!isset($_SESSION['correo']) || $_SESSION['rol'] != 'Tecnico') {
    header("Location: /equipo3/index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Técnico</title>
    <link rel="stylesheet" href="/equipo3/styles/styles_tecnico.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>

<body>

    <div class="sidebar">
        <a href="tecnico_home.php"><i class="fa-solid fa-house"></i> Home</a>
        <a href="#"><i class="fa-solid fa-box"></i> Pedido</a>
        <a href="#"><i class="fa-solid fa-truck"></i> Envío</a>
        <a class="logout-btn" href="/equipo3/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
    </div>

    <div class="main-content">
        <h2>Bienvenido, Técnico</h2>

        <!-- Tabla del registro del Técnico -->
        <h3>Registro del Técnico</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Fecha</th>
            </tr>

        </table>

        <!-- Tabla de clientes que solicitaron servicios -->
        <h3>Clientes que solicitaron servicios de aire acondicionado</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre del Cliente</th>
                <th>Correo</th>
                <th>Servicio Solicitado</th>
                <th>Fecha de Solicitud</th>
            </tr>

        </table>
    </div>

</body>
</html>