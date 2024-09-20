<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Vuelos</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Estilo del contenedor */
        .container {
            max-width: 800px; /* Ancho máximo del contenedor */
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9); /* Fondo blanco con transparencia */
            border-radius: 8px; /* Bordes redondeados */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* Sombra suave */
        }

        /* Estilo de la barra de selección */
        label {
            display: block;
            margin-bottom: 5px;
        }

        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 15px; /* Espacio debajo del select */
        }

        input[type="submit"] {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            background-color: #4CAF50; /* Color de fondo */
            color: white; /* Color de texto */
            cursor: pointer; /* Cambiar cursor al pasar el mouse */
        }

        input[type="submit"]:hover {
            background-color: #45a049; /* Color al pasar el mouse */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Búsqueda de Vuelos</h1>
        <form action="" method="POST">
            <label for="flight_id">Selecciona un Vuelo:</label>
            <select id="flight_id" name="flight_id" required>
                <?php
                // Conexión a la base de datos
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "flight_reservation";

                $conn = new mysqli($servername, $username, $password, $dbname);
                if ($conn->connect_error) {
                    die("Conexión fallida: " . $conn->connect_error);
                }

                // Consultar vuelos disponibles
                $sql = "SELECT flight_id, origin, destination, departure_date, time, price FROM Flights";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['flight_id']}'>
                                Vuelo de {$row['origin']} a {$row['destination']} 
                                (Fecha: {$row['departure_date']}, Hora: {$row['time']}, Precio: \${$row['price']})
                              </option>";
                    }
                } else {
                    echo "<option value=''>No hay vuelos disponibles</option>";
                }

                $conn->close();
                ?>
            </select>

            <input type="submit" value="Reservar Vuelo">
        </form>

        <?php
        // Manejar la reserva del vuelo
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['flight_id'])) {
            $flight_id = $_POST['flight_id'];
            $user_id = $_SESSION['user_id'];
            $user_name = $_SESSION['user_name'];

            // Conexión a la base de datos
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Conexión fallida: " . $conn->connect_error);
            }

            // Insertar la reserva en la base de datos
            $stmt = $conn->prepare("INSERT INTO reservations (user_id, flight_id, user_name) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $user_id, $flight_id, $user_name);

            if ($stmt->execute()) {
                // Redirigir a la página de confirmación de la reserva
                header("Location: view_reservations.php?flight_id=" . $flight_id);
                exit();
            } else {
                echo "Error al realizar la reserva: " . $conn->error;
            }

            $stmt->close();
            $conn->close();
        }
        ?>
        <div class="toggle-link">
        <p><a href="login.html">Regresar al login</a></p>
    </div>
    </div>
</body>
</html>
