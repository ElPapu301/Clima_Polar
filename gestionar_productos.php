<?php
session_start();

// Verificar si la sesión está activa
if (!isset($_SESSION['correo'])) {
    header("Location: index.php");
    exit();
}

include 'bd_conexion.php'; // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validación y sanitización de los datos de entrada
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
    $categoria_id = isset($_POST['categoria_id']) ? intval($_POST['categoria_id']) : 0;
    $proveedorID = isset($_POST['proveedorID']) ? intval($_POST['proveedorID']) : 0;

    // Manejo de imagen
    $imagen = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Validar que el archivo es una imagen
        $imagenTmp = $_FILES['imagen']['tmp_name'];
        $imagenTipo = mime_content_type($imagenTmp);
        if (in_array($imagenTipo, ['image/jpeg', 'image/png', 'image/gif'])) {
            $imagen = '/equipo3/imagenes/' . basename($_FILES['imagen']['name']);
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $imagen)) {
                die("Error al mover la imagen al directorio.");
            }
        } else {
            die("El archivo no es una imagen válida.");
        }
    }

    // Comprobar si se ha enviado la acción
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Determinar si se está agregando o editando
    if ($action == "Agregar") {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, categoria_id, fechaIngreso, proveedorID, imagen) 
                VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmt->bind_param("ssdiiis", $nombre, $descripcion, $precio, $cantidad, $categoria_id, $proveedorID, $imagen);
    } elseif ($action == "Editar") {
        $productoID = isset($_POST['productoID']) ? intval($_POST['productoID']) : 0;
        $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria_id = ?, proveedorID = ?, imagen = ? WHERE productoID = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmt->bind_param("ssdiissi", $nombre, $descripcion, $precio, $cantidad, $categoria_id, $proveedorID, $imagen, $productoID);
    }

    // Ejecutar la declaración
    if (isset($stmt)) {
        if (!$stmt->execute()) {
            die("Error en la ejecución de la consulta: " . $stmt->error);
        }
        // Redirigir o mostrar un mensaje
        header("Location: gestionar_productos.php");
        exit();
    }
}

// Manejo de eliminación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    // Obtener el ID del producto a eliminar
    $productoID = $_POST['productoID'];

    // Preparar la consulta SQL para eliminar
    $sqlEliminar = "DELETE FROM productos WHERE productoID = ?";
    $stmtEliminar = $conn->prepare($sqlEliminar);
    if (!$stmtEliminar) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    // Enlazar el parámetro
    $stmtEliminar->bind_param("i", $productoID);

    // Ejecutar la declaración
    if (!$stmtEliminar->execute()) {
        die("Error en la ejecución de la consulta: " . $stmtEliminar->error);
    }

    // Redirigir o mostrar un mensaje
    header("Location: gestionar_productos.php");
    exit();
}

// Obtener detalles del producto a editar si se solicitó
$productoEditar = null;
if (isset($_GET['editar'])) {
    $productoID = $_GET['editar'];
    $sqlEditar = "SELECT * FROM productos WHERE productoID = ?";
    $stmtEditar = $conn->prepare($sqlEditar);
    $stmtEditar->bind_param("i", $productoID);
    $stmtEditar->execute();
    $productoEditar = $stmtEditar->get_result()->fetch_assoc();
    $stmtEditar->close();
}

// Obtener la lista de productos
$result = $conn->query("SELECT p.productoID, p.nombre, p.descripcion, p.precio, p.stock, c.nombre_categoria 
as categoria, p.fechaIngreso, pr.nombre 
as proveedor, p.imagen 
FROM productos p JOIN categorias c 
ON p.categoria_id = c.id 
JOIN proveedores pr ON p.proveedorID = pr.proveedorID");

?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Productos</title>
    <link rel="stylesheet" href="/equipo3/styles/styles_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

</head>
<body>
<div class="sidebar">
    <a href="usuarios/admin_home.php?tipo=todos"><i class="fa-solid fa-house"></i> Inicio</a>
    <a href="usuarios/admin_home.php?tipo=empleados"><i class="fa-solid fa-user"></i> Gestionar Empleados</a>
        <a href="usuarios/admin_home.php?tipo=clientes"><i class="fa-solid fa-user"></i> Gestionar Clientes</a>
        <a href="gestionar_productos.php"><i class="fa-brands fa-product-hunt"></i> Gestionar Productos</a>
        <a href="gestionar_servicios.php"><i class="fa-solid fa-cogs"></i> Gestionar Servicios</a>

        <a class="logout-btn" href="/equipo3/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
    </div>

    <div class="main-content">
    <h1>ADMINISTRADOR</h1>
        <h3>Lista De Productos</h3>
        <table>
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>Categoría</th>
        <th>Fecha de Ingreso</th>
        <th>Proveedor</th>
        <th>Imagen</th>
        <th>Acciones</th>
    </tr>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['productoID']); ?></td>
                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                <td><?php echo htmlspecialchars($row['precio']); ?></td>
                <td><?php echo htmlspecialchars($row['stock']); ?></td>
                <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                <td><?php echo htmlspecialchars($row['fechaIngreso']); ?></td>
                <td><?php echo htmlspecialchars($row['proveedor']); ?></td>
                <td><img src="<?php echo htmlspecialchars($row['imagen']); ?>" alt="Imagen del producto" width="50"></td>
                <td>
                    <a href="?editar=<?php echo $row['productoID']; ?>" class="btn-editar"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                    <form method="POST" action="gestionar_productos.php" style="display:inline;">
                        <input type="hidden" name="productoID" value="<?php echo $row['productoID']; ?>">
                        <button type="submit" name="eliminar" class="btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                            <i class="fa-regular fa-trash-can"></i> Eliminar
                        </button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="10">No hay productos disponibles.</td>
        </tr>
    <?php endif; ?>
</table>

        <!-- Formulario para Agregar Producto -->
        <div class="form-editar-container">
            <h3>Agregar Producto</h3>
            <form action="gestionar_productos.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="Agregar">
                
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" required>

                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" class="descripcion-textarea" name="descripcion" required></textarea>


                <label for="precio">Precio:</label>
                <input type="number" name="precio" step="0.01" required>

                <label for="cantidad">Cantidad:</label>
                <input type="number" name="cantidad" required>

                <label for="categoria">Categoría:</label>
                <select name="categoria_id" required>
                    <?php
                    $sqlCategorias = "SELECT id, nombre_categoria FROM categorias";
                    $resultCategorias = $conn->query($sqlCategorias);
                    while ($row = $resultCategorias->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['nombre_categoria']}</option>";
                    }
                    ?>
                </select>

                <label for="proveedor">Proveedor:</label>
                <select name="proveedorID" required>
                    <?php
                    $sqlProveedores = "SELECT proveedorID, nombre FROM proveedores";
                    $resultProveedores = $conn->query($sqlProveedores);
                    while ($row = $resultProveedores->fetch_assoc()) {
                        echo "<option value='{$row['proveedorID']}'>{$row['nombre']}</option>";
                    }
                    ?>
                </select>

            <label for="imagen">Imagen:</label>
                <input type="file" name="imagen" accept="image/*" id="imagen" class="input-file">
                <label for="imagen" class="input-file-btn">
                <i class="fa-solid fa-upload"></i> Subir Imagen
            </label>

                <button type="submit" class="btn-registrar">Agregar Producto <i class="fa-solid fa-check"></i></button>
            </form>
        </div>

        <!-- Formulario para Editar Producto -->
        <?php if ($productoEditar): ?>
        <div class="form-editar-container">
            <h3>Editar Producto</h3>
            <form action="gestionar_productos.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="Editar">
                <input type="hidden" name="productoID" value="<?= $productoEditar['productoID'] ?>">

                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" value="<?= $productoEditar['nombre'] ?>" required>

                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" class="descripcion-textarea" required><?= $productoEditar['descripcion'] ?></textarea>

                <label for="precio">Precio:</label>
                <input type="number" name="precio" step="0.01" value="<?= $productoEditar['precio'] ?>" required>

                <label for="cantidad">Cantidad:</label>
                <input type="number" name="cantidad" value="<?= $productoEditar['stock'] ?>" required>

                <label for="categoria">Categoría:</label>
                <select name="categoria_id" required>
                    <?php
                    $sqlCategorias = "SELECT id, nombre_categoria FROM categorias";
                    $resultCategorias = $conn->query($sqlCategorias);
                    while ($row = $resultCategorias->fetch_assoc()) {
                        $selected = ($row['id'] == $productoEditar['categoria_id']) ? "selected" : "";
                        echo "<option value='{$row['id']}' {$selected}>{$row['nombre_categoria']}</option>";
                    }
                    ?>
                </select>

                <label for="proveedor">Proveedor:</label>
                <select name="proveedorID" required>
                    <?php
                    $sqlProveedores = "SELECT proveedorID, nombre FROM proveedores";
                    $resultProveedores = $conn->query($sqlProveedores);
                    while ($row = $resultProveedores->fetch_assoc()) {
                        $selected = ($row['proveedorID'] == $productoEditar['proveedorID']) ? "selected" : "";
                        echo "<option value='{$row['proveedorID']}' {$selected}>{$row['nombre']}</option>";
                    }
                    ?>
                </select>

            <label for="imagen">Imagen:</label>
                <input type="file" name="imagen" accept="image/*" id="imagen" class="input-file">
                <label for="imagen" class="input-file-btn">
                <i class="fa-solid fa-upload"></i> Subir Imagen
            </label>


                <button type="submit" name="guardar_cambios">Actualizar Producto <i class="fa-solid fa-pen"></i></button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

