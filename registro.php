<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'bd_conexion.php';

$message = "";

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre']; // Obtener nombre del formulario
    $email = $_POST['email'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);
    $rol = $_POST['rol']; // Obtener rol del formulario

    // Verificar si el email ya existe
    $checkEmailStmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->bind_result($emailCount);
    $checkEmailStmt->fetch();
    $checkEmailStmt->close();

    if ($emailCount > 0) {
        $message = "Error: El correo electrónico ya está registrado.";
    } else {
        // Insertar nuevo usuario con el rol seleccionado y nombre
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $contraseña, $rol);

        if ($stmt->execute()) {
            $message = "¡Registro exitoso! Haz clic en 'Iniciar sesión' para continuar.";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles_index.css">

    <title>Registro de Cliente</title>
</head>
<body>
    <div class="login-container">
        <h2>Registro de Cliente</h2>
        <?php if (!empty($message)) : ?>
            <div class="error"><?= $message; ?></div>
        <?php endif; ?>
        <form action="registro.php" method="post">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" name="contraseña" required>
            </div>
            <div class="form-group">
                <label for="rol">Rol:</label>
                <select id="rol" name="rol" required>
                    <option value="Cliente">Cliente</option>
                    <option value="Admin">Admin</option>
                    <option value="Secretaria_Ventas">Secretaria de Ventas</option>
                    <option value="Secretaria_Compras">Secretaria de Compras</option>
                    <option value="Tecnico">Técnico</option>
                </select>
            </div>
            <button type="submit">Registrar</button>
        </form>
        <a href="index.php">¿Ya tienes cuenta? Inicia sesión aquí.</a>
    </div>
</body>
</html>
