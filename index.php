<?php
session_start(); // Inicia la sesión
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('bd_conexion.php');

// Verifica si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $conn->real_escape_string($_POST['email']);
    $contrasena = $conn->real_escape_string($_POST['password']);

    $_SESSION['mensaje_error'] = ""; // Inicializa el mensaje de error

    // Consulta para verificar el usuario
    $consultaUsuario = "SELECT * FROM usuarios WHERE correo = ?";
    $stmtUsuario = $conn->prepare($consultaUsuario);

    if ($stmtUsuario === false) {
        die("Error en la consulta SQL: " . $conn->error);
    }

    $stmtUsuario->bind_param("s", $correo);
    $stmtUsuario->execute();
    $resultadoUsuario = $stmtUsuario->get_result();

    if ($resultadoUsuario && $resultadoUsuario->num_rows > 0) {
        $usuario = $resultadoUsuario->fetch_assoc();
    
        if (password_verify($contrasena, $usuario['password'])) {
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirigir según el rol del usuario
            switch ($usuario['rol']) {
                case 'Admin':
                    header("Location: usuarios/admin_home.php");
                    break;
                case 'Secretaria_Ventas':
                    header("Location: usuarios/secretaria_ventas.php");
                    break;
                case 'Secretaria_Compras':
                    header("Location: usuarios/secretaria_compras.php");
                    break;
                case 'Cliente':
                    header("Location: usuarios/cliente_home.php");
                    break;
                case 'Tecnico':
                    header("Location: usuarios/tecnico_home.php");
                    break;
            }
            exit();
        } else {
            $_SESSION['mensaje_error'] = "Contraseña incorrecta.";
        }
    } else {
        $_SESSION['mensaje_error'] = "No se encontró una cuenta con ese correo electrónico.";
    }

    header("Location: index.php"); // Redirigir de vuelta al login
    exit();
}


// Cerrar conexión si es necesario
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="styles/styles_index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

</head>
<body>
    <div class="login-container">
        <img src="imagenes/logo.jpeg" alt="Logo de la Empresa" class="logo">
        <h2>Inicio de Sesión</h2>
        <div class="error">
            <?php
            // Mostrar mensaje de error
            if (isset($_SESSION['mensaje_error']) && $_SESSION['mensaje_error'] != "") {
                echo htmlspecialchars($_SESSION['mensaje_error']);
                unset($_SESSION['mensaje_error']); // Limpia el mensaje de error después de mostrarlo
            }
            ?>
        </div>
        <form action="index.php" method="POST">
            <input type="text" name="email" placeholder="Correo electrónico"  required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit"> Iniciar Sesión</button>
        </form>
        <form action="registro.php" style="margin-top: 10px;">
            <button type="submit">Registrarme</button>
        </form>
    </div>
</body>
</html>
