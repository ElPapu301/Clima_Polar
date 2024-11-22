<?php
session_start(); // Ensure the session is started

// Redirect if the user is not logged in
if (!isset($_SESSION['correo'])) {
    header("Location: /equipo3/index.php");
    exit();
}

include('../bd_conexion.php'); // Include database connection

// Function to handle user feedback
function setFlashMessage($message) {
    $_SESSION['flash_message'] = $message;
}

// Handle actions on the page (edit, delete, register)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $correo = $_POST['correo'] ?? null;
    $rol = $_POST['rol'] ?? null;
    $password = $_POST['password'] ?? null; // Add password variable

    // Update user
    if (isset($_POST['guardar_cambios']) && $id) {
        // Check if password is provided
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nombre, $correo, $rol, $password_hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nombre, $correo, $rol, $id);
        }

        if ($stmt->execute()) {
            setFlashMessage('Usuario actualizado correctamente.');
        } else {
            setFlashMessage('Error al actualizar el usuario: ' . $stmt->error);
        }
        $stmt->close();
        header("Location: admin_home.php");
        exit();
    }

    // Delete user
    if (isset($_POST['eliminar_usuario']) && $id) {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            setFlashMessage('Usuario eliminado correctamente.');
        } else {
            setFlashMessage('Error al eliminar el usuario: ' . $stmt->error);
        }
        $stmt->close();
        header("Location: admin_home.php");
        exit();
    }

    // Register new user
    if (isset($_POST['registrar_usuario'])) {
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, rol, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $correo, $rol, $password_hash);
        
        if ($stmt->execute()) {
            setFlashMessage('Usuario registrado correctamente.');
        } else {
            setFlashMessage('Error al registrar el usuario: ' . $stmt->error);
        }
        $stmt->close();
        header("Location: admin_home.php");
        exit();
    }
}

$tipo_usuario = $_GET['tipo'] ?? '';
$query = "SELECT id, nombre, correo, rol, fecha FROM usuarios";

switch ($tipo_usuario) {
    case 'clientes':
        $query .= " WHERE rol = 'Cliente'";
        $titulo = "Lista De Clientes";
        break;
    case 'empleados':
        $query .= " WHERE rol IN ('Secretaria_Ventas', 'Secretaria_Compras', 'Tecnico')";
        $titulo = "Lista De Empleados";
        break;
    case 'todos':
    default:
        $titulo = "Lista De Usuarios";
        break;
}

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
    <title>Administrador</title>
    <link rel="stylesheet" href="/equipo3/styles/styles_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>

    <div class="sidebar">
        <a href="admin_home.php?tipo=todos"><i class="fa-solid fa-house"></i> Inicio</a>
        <a href="admin_home.php?tipo=empleados"><i class="fa-solid fa-user"></i> Gestionar Empleados</a>
        <a href="admin_home.php?tipo=clientes"><i class="fa-solid fa-user"></i> Gestionar Clientes</a>
        <a href="/equipo3/gestionar_productos.php"><i class="fa-brands fa-product-hunt"></i> Gestionar Productos</a>
        <a href="../gestionar_servicios.php"><i class="fa-solid fa-cogs"></i> Gestionar Servicios</a>
        <a class="logout-btn" href="/equipo3/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
    </div>

    <div class="main-content">
        <h1>ADMINISTRADOR</h1>
        <h3><?php echo $titulo; ?></h3>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert"><?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?></div>
        <?php endif; ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['correo']); ?></td>
                    <td><?php echo htmlspecialchars($row['rol']); ?></td>
                    <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                    <td>
                        <a href="admin_home.php?editar_id=<?php echo $row['id']; ?>" class="btn-editar"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                        <form action="admin_home.php" method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="eliminar_usuario" class="btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');"><i class="fa-regular fa-trash-can"></i> Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <div class="form-registrar-container">
            <h3>Registrar Nuevo Usuario</h3>
            <form action="admin_home.php" method="post">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>

                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" required>

                <label for="rol">Rol</label>
                <select id="rol" name="rol" required>
                    <option value="Cliente">Cliente</option>
                    <option value="Secretaria_Ventas">Secretaria Ventas</option>
                    <option value="Secretaria_Compras">Secretaria Compras</option>
                    <option value="Tecnico">Técnico</option>
                    <option value="Admin">Admin</option>
                </select>

                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" name="registrar_usuario" class="btn-registrar">Registrar Usuario <i class="fa-solid fa-check"></i></button>
            </form>
        </div>

        <?php if (isset($_GET['editar_id'])):
            $editar_id = $_GET['editar_id'];
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $editar_id);
            $stmt->execute();
            $usuario = $stmt->get_result()->fetch_assoc();
        ?>
        <div class logout-btn>
        
        </div>

            <form class="form-editar-container" action="admin_home.php" method="POST">
                <h3>Editar Usuario</h3>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id']); ?>">

                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>

                <label for="correo">Correo:</label>
                <input type="email" name="correo" id="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>

                <label for="rol">Rol:</label>
                <select name="rol" id="rol" required>
                    <option value="Cliente" <?php echo ($usuario['rol'] == 'Cliente') ? 'selected' : ''; ?>>Cliente</option>
                    <option value="Secretaria_Ventas" <?php echo ($usuario['rol'] == 'Secretaria_Ventas') ? 'selected' : ''; ?>>Secretaria de Ventas</option>
                    <option value="Secretaria_Compras" <?php echo ($usuario['rol'] == 'Secretaria_Compras') ? 'selected' : ''; ?>>Secretaria de Compras</option>
                    <option value="Tecnico" <?php echo ($usuario['rol'] == 'Tecnico') ? 'selected' : ''; ?>>Técnico</option>
                </select>

                <label for="password">Nueva Contraseña (dejar en blanco si no deseas cambiarla):</label>
                <input type="password" name="password" id="password">
                <button type="submit" name="guardar_cambios">Guardar Cambios <i class="fa-solid fa-pen"></i></button>
                <button type="submit" class="eliminar" name="eliminar_usuario">Eliminar Usuario <i class="fa-regular fa-trash-can"></i></button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
