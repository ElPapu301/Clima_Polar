<?php
session_start(); // Asegura que la sesión esté activa

// Redirige si el usuario no está logueado
if (!isset($_SESSION['correo'])) {
    header("Location: /equipo3/index.php");
    exit();
}

include('bd_conexion.php'); // Incluir conexión a la base de datos

// Función para manejar los mensajes flash
function setFlashMessage($message) {
    $_SESSION['flash_message'] = $message;
}

// Manejar acciones en la página (editar, eliminar, registrar)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre_servicio = $_POST['nombre_servicio'] ?? null;
    $descripcion = $_POST['descripcion'] ?? null;
    $precio = $_POST['precio'] ?? null;

    // Registrar nuevo servicio
    if (isset($_POST['registrar_servicio'])) {
        $stmt = $conn->prepare("INSERT INTO servicios (nombre_servicio, descripcion, precio) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $nombre_servicio, $descripcion, $precio);

        if ($stmt->execute()) {
            setFlashMessage('Servicio registrado correctamente.');
        } else {
            setFlashMessage('Error al registrar el servicio: ' . $stmt->error);
        }
        $stmt->close();
        header("Location: gestionar_servicios.php");
        exit();
    }

    // Eliminar servicio
    if (isset($_POST['eliminar_servicio']) && $id) {
        $stmt = $conn->prepare("DELETE FROM servicios WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            setFlashMessage('Servicio eliminado correctamente.');
        } else {
            setFlashMessage('Error al eliminar el servicio: ' . $stmt->error);
        }
        $stmt->close();
        header("Location: gestionar_servicios.php");
        exit();
    }

    // Editar servicio
    if (isset($_POST['guardar_cambios']) && $id) {
        $stmt = $conn->prepare("UPDATE servicios SET nombre_servicio = ?, descripcion = ?, precio = ? WHERE id = ?");
        $stmt->bind_param("ssdi", $nombre_servicio, $descripcion, $precio, $id);

        if ($stmt->execute()) {
            setFlashMessage('Servicio actualizado correctamente.');
        } else {
            setFlashMessage('Error al actualizar el servicio: ' . $stmt->error);
        }
        $stmt->close();
        header("Location: gestionar_servicios.php");
        exit();
    }
}

$query = "SELECT id, nombre_servicio, descripcion, precio FROM servicios";
$result = $conn->query($query);
if (!$result) {
    die("Error en la consulta SQL: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Servicios</title>
    <link rel="stylesheet" href="/equipo3/styles/styles_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>

    <div class="sidebar">
        <a href="usuarios/admin_home.php?tipo=todos"><i class="fa-solid fa-house"></i> Inicio</a>
        <a href="usuarios/admin_home.php?tipo=empleados"><i class="fa-solid fa-user"></i> Gestionar Empleados</a>
        <a href="usuarios/admin_home.php?tipo=clientes"><i class="fa-solid fa-user"></i> Gestionar Clientes</a>
        <a href="/equipo3/gestionar_productos.php"><i class="fa-brands fa-product-hunt"></i> Gestionar Productos</a>
        <a href="gestionar_servicios.php"><i class="fa-solid fa-cogs"></i> Gestionar Servicios</a>
        <a class="logout-btn" href="/equipo3/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
    </div>

    <div class="main-content">
    <h1>ADMINISTRADOR</h1>
        <h3>Lista De Servicios</h3>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert"><?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?></div>
        <?php endif; ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Nombre del Servicio</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['nombre_servicio']); ?></td>
                    <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($row['precio']); ?></td>
                    <td>
                        <a href="gestionar_servicios.php?editar_id=<?php echo $row['id']; ?>" class="btn-editar"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                        <form action="gestionar_servicios.php" method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="eliminar_servicio" class="btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este servicio?');"><i class="fa-regular fa-trash-can"></i> Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <div class="form-registrar-container">
            <h3>Registrar Nuevo Servicio</h3>
            <form action="gestionar_servicios.php" method="post">
                <label for="nombre_servicio">Nombre del Servicio</label>
                <input type="text" id="nombre_servicio" name="nombre_servicio" required>

                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" class="descripcion-textarea" name="descripcion" required></textarea>

                <label for="precio">Precio</label>
                <input type="number" id="precio" name="precio" step="0.01" required>

                <button type="submit" name="registrar_servicio" class="btn-registrar">Registrar Servicio <i class="fa-solid fa-check"></i></button>
            </form>
        </div>

        <?php if (isset($_GET['editar_id'])):
            $editar_id = $_GET['editar_id'];
            $stmt = $conn->prepare("SELECT * FROM servicios WHERE id = ?");
            $stmt->bind_param("i", $editar_id);
            $stmt->execute();
            $servicio = $stmt->get_result()->fetch_assoc();
        ?>
        <form class="form-editar-container" action="gestionar_servicios.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($servicio['id']); ?>">
            
            <label for="nombre_servicio">Nombre del Servicio:</label>
            <input type="text" name="nombre_servicio" id="nombre_servicio" value="<?php echo htmlspecialchars($servicio['nombre_servicio']); ?>" required>

            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" class="descripcion-textarea"  id="descripcion" required><?php echo htmlspecialchars($servicio['descripcion']); ?></textarea>

            <label for="precio">Precio:</label>
            <input type="number" name="precio" id="precio" value="<?php echo htmlspecialchars($servicio['precio']); ?>" step="0.01" required>

            <button type="submit" name="guardar_cambios">Guardar Cambios <i class="fa-solid fa-pen"></i></button>
            <button type="submit" class="eliminar" name="eliminar_servicio">Eliminar Servicio <i class="fa-regular fa-trash-can"></i></button>
        </form>

        <?php endif; ?>
    </div>
</body>
</html>
