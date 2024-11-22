<?php
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: /equipo3/index.php");
    exit();
}

include '../bd_conexion.php';

$categoria_id = isset($_POST['categoria_id']) ? intval($_POST['categoria_id']) : 0;
$servicio_id = isset($_POST['servicio_id']) ? intval($_POST['servicio_id']) : 0;

$sqlProductos = "SELECT p.productoID, p.nombre, p.descripcion, p.precio, p.stock, c.nombre_categoria, p.fechaIngreso, pr.nombre as proveedor, p.imagen 
                FROM productos p 
                JOIN categorias c ON p.categoria_id = c.id 
                JOIN proveedores pr ON p.proveedorID = pr.proveedorID";
if ($categoria_id != 0) {
    $sqlProductos .= " WHERE c.id = ?";
}

$stmtProductos = $conn->prepare($sqlProductos);
if ($categoria_id != 0) {
    $stmtProductos->bind_param("i", $categoria_id);
}
$stmtProductos->execute();
$resultadoProductos = $stmtProductos->get_result();

$sqlServicios = "SELECT id, nombre_servicio, descripcion, precio FROM servicios";
if ($servicio_id != 0) {
    $sqlServicios .= " WHERE id = ?";
}

$stmtServicios = $conn->prepare($sqlServicios);
if ($servicio_id != 0) {
    $stmtServicios->bind_param("i", $servicio_id);
}
$stmtServicios->execute();
$resultadoServicios = $stmtServicios->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Tienda | Clima Polar</title>
    <link rel="stylesheet" href="../styles/styles_clientes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow">
        <div class="container-fluid">
            <a class="navbar-brand" href="cliente_home.php"><i class="fa-solid fa-house"></i> Inicio</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <form method="POST" class="d-flex">
                            <label for="categoria-selector" class="form-label me-2"><i class="fa-solid fa-list"></i> Categoría:</label>
                            <select id="categoria-selector" name="categoria_id" class="form-select" onchange="this.form.submit()">
                                <option value="0">Todas las categorías</option>
                                <?php
                                $sqlCategorias = "SELECT id, nombre_categoria FROM categorias";
                                $resultadoCategorias = $conn->query($sqlCategorias);
                                while ($fila = $resultadoCategorias->fetch_assoc()) {
                                    $seleccionado = ($fila['id'] == $categoria_id) ? "selected" : "";
                                    echo "<option value='{$fila['id']}' $seleccionado>{$fila['nombre_categoria']}</option>";
                                }
                                ?>
                            </select>
                        </form>
                    </li>
                    <li class="nav-item">
                        <form method="POST" class="d-flex ms-3">
                            <label for="servicio-selector" class="form-label me-2"><i class="fa-brands fa-creative-commons-nd"></i> Servicios:</label>
                            <select id="servicio-selector" name="servicio_id" class="form-select" onchange="this.form.submit()">
                                <option value="0">Todos los servicios</option>
                                <?php
                                $sqlServiciosLista = "SELECT id, nombre_servicio FROM servicios";
                                $resultadoServiciosLista = $conn->query($sqlServiciosLista);
                                while ($fila = $resultadoServiciosLista->fetch_assoc()) {
                                    $seleccionado = ($fila['id'] == $servicio_id) ? "selected" : "";
                                    echo "<option value='{$fila['id']}' $seleccionado>{$fila['nombre_servicio']}</option>";
                                }
                                ?>
                            </select>
                        </form>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/equipo3/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="text-center">Bienvenido a Clima Polar</h2>
        <p class="text-center">Explora nuestra amplia gama de productos y servicios para el mantenimiento y reparación de aire acondicionado.</p>
        <h3 class="mt-5">Productos</h3>
        <div class="row">
            <?php if ($resultadoProductos && $resultadoProductos->num_rows > 0): ?>
                <?php while ($fila = $resultadoProductos->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card custom-card">
                            <img src="<?php echo htmlspecialchars($fila['imagen']); ?>" class="card-img-top" alt="Imagen de <?php echo htmlspecialchars($fila['nombre']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($fila['nombre']); ?></h5>
                                <p class="card-text"><strong>Descripción:</strong> <?php echo htmlspecialchars($fila['descripcion']); ?></p>
                                <p class="card-text"><strong>Precio:</strong> $<?php echo number_format($fila['precio'], 2); ?></p>
                                <p class="card-text"><strong>Stock:</strong> <?php echo htmlspecialchars($fila['stock']); ?></p>
                                <a href="#" class="btn btn-primary">Reservar</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No hay productos disponibles en esta categoría.</p>
            <?php endif; ?>
        </div>

        <h3 class="mt-5">Servicios</h3>
        <div class="row">
            <?php if ($resultadoServicios && $resultadoServicios->num_rows > 0): ?>
                <?php while ($fila = $resultadoServicios->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card custom-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($fila['nombre_servicio']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($fila['descripcion']); ?></p>
                                <p class="card-text"><strong>Precio:</strong> $<?php echo number_format($fila['precio'], 2); ?></p>
                                <a href="#" class="btn btn-primary">Reservar</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No hay servicios disponibles.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-light text-center py-4 mt-5">
        <div class="container">
            <p>Contacto <i class="fa-solid fa-envelope"></i>: clima_polar@gmail.com</p>
            <p>Teléfono <i class="fa-solid fa-phone-flip"></i>: 7555544501</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

