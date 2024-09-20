<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "flight_reservation";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_reservations = [];
$entered_username = "";  // Variable para almacenar el nombre de usuario ingresado

// Verificar si se ha enviado el nombre de usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    $entered_username = $_POST['username'];

    // Consultar todas las reservas para ese usuario
    $sql = "SELECT r.flight_id, f.origin, f.destination, f.departure_date, f.time, f.price 
            FROM reservations r 
            JOIN Flights f ON r.flight_id = f.flight_id
            JOIN Users u ON r.user_id = u.user_id
            WHERE u.username = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $entered_username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si hay resultados, almacenarlos en la variable $user_reservations
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $user_reservations[] = $row;
        }
    } else {
        $message = "No se encontraron reservas para el usuario $entered_username.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Reservas</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Estilo del contenedor */
        .reservations-container {
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9); /* Fondo blanco con transparencia */
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* Sombra suave */
            max-width: 800px; /* Ancho máximo del contenedor */
        }

        /* Tabla en view_reservations.php */
        table {
            width: 100%; /* Ancho completo de la tabla */
            border-collapse: collapse; /* Colapsar bordes de la tabla */
            margin-top: 20px; /* Espacio antes de la tabla */
        }

        th, td {
            border: 1px solid #dddddd; /* Bordes de las celdas */
            text-align: left; /* Alinear texto a la izquierda */
            padding: 8px; /* Espaciado interno de las celdas */
        }

        th {
            background-color: #f1f1f1; /* Fondo gris claro para encabezados */
            color: #333333; /* Color de texto para encabezados */
        }

        tr:nth-child(even) {
            background-color: #f9f9f9; /* Fondo alternativo para filas pares */
        }

        tr:hover {
            background-color: #e0e0e0; /* Resaltar fila al pasar el mouse */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ver Reservas de Vuelo</h1>

        <!-- Mostrar el formulario solo si no se ha ingresado un nombre de usuario -->
        <?php if (empty($entered_username)): ?>
            <div class="form-container">
                <form action="" method="POST">
                    <label for="username">Escribe el nombre de usuario para ver sus vuelos reservados:</label><br>
                    <input type="text" id="username" name="username" required><br><br>
                    <input type="submit" value="Buscar Reservas">
                </form>
            </div>
        <?php endif; ?>

        <hr>

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <!-- Verificar si hay reservas para mostrar -->
            <?php if (!empty($user_reservations)): ?>
                <h2>Reservas del usuario: <?php echo htmlspecialchars($entered_username); ?></h2>
                <div class="reservations-container">
                    <table>
                        <tr>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Precio</th>
                        </tr>
                        <?php foreach ($user_reservations as $reservation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['origin']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['destination']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['departure_date']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['time']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['price']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <div class="toggle-link">
        <p><a href="search.html">Regresar a buscar vuelos</a></p>
    </div>
    </div>
</body>
</html>
