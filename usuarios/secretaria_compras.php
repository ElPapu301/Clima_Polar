<?php
session_start();

// Verificar si la sesión está activa
if (!isset($_SESSION['correo'])) {
    header("Location: /equipo3/index.php");
    exit();
}

// Incluir conexión a la base de datos
include('../bd_conexion.php');

// Obtener el correo de la sesión activa
$correo = $_SESSION['correo'];

// Consulta para obtener los datos del usuario activo
$sql = "SELECT id, nombre, correo, rol, password, fecha FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretaria Compras | Clima Polar</title>
    <link rel="stylesheet" href="/equipo3/styles/styles_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Barra lateral -->
    <div class="sidebar">
        <a href="secretaria_compras.php"><i class="fa-solid fa-house"></i> Inicio</a>
        <a href="#"><i class="fa-solid fa-file-invoice-dollar"></i> Compra y Ventas</a>
        <div class="submenu">
            <a href="#">Pagos</a>
            <a href="#">Ventas</a>
            <a href="#">Compras</a>
        </div>
        <a href="#"><i class="fa-solid fa-shipping-fast"></i> Pedidos y Envíos</a>
        <div class="submenu">
            <a href="#">Transporte</a>
            <a href="#">Clientes</a>
            <a href="#">Envíos</a>
        </div>
        <a href="#"><i class="fa-solid fa-file-invoice-dollar"></i> Catalogar</a>
        <div class="submenu">
            <a href="#">Productos</a>
            <a href="#">Categorías</a>
        </div>
        <a class="logout-btn" href="/equipo3/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
    </div>

    <div class="main-content">
        <h3>SECRETARIA VENTAS</h3>
        <h4>Información del Usuario</h4>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($usuario): ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario['id']) ?></td>
                        <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                        <td><?= htmlspecialchars($usuario['correo']) ?></td>
                        <td><?= htmlspecialchars($usuario['rol']) ?></td>
                        <td><?= htmlspecialchars($usuario['fecha']) ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No se encontraron datos para el usuario activo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
