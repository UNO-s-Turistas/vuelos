<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Usuario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Ingrese su Nombre de Usuario</h1>
        <form action="reserve_flights.php" method="POST">
            <label for="user_name">Nombre de Usuario:</label>
            <input type="text" id="user_name" name="user_name" required><br><br>
            <input type="hidden" name="flight_id" value="<?php echo htmlspecialchars($_GET['flight_id']); ?>">
            <input type="submit" value="Continuar">
        </form>

        <?php
        // Manejar la validación del usuario
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_name'])) {
            $user_name = $_POST['user_name'];
            $flight_id = $_POST['flight_id'];

            // Conexión a la base de datos
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "flight_reservation";

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Conexión fallida: " . $conn->connect_error);
            }

            // Verificar si el usuario ya existe
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_name = ?");
            $stmt->bind_param("s", $user_name);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Usuario existe, obtener su ID
                $row = $result->fetch_assoc();
                $user_id = $row['user_id'];
            } else {
                // Usuario no existe, insertarlo
                $stmt = $conn->prepare("INSERT INTO users (user_name) VALUES (?)");
                $stmt->bind_param("s", $user_name);
                $stmt->execute();
                $user_id = $stmt->insert_id; // Obtener el ID del nuevo usuario
            }

            $stmt->close();

            // Insertar en la tabla reservations
            $stmt = $conn->prepare("INSERT INTO reservations (user_id, flight_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $flight_id);
            $stmt->execute();
            $stmt->close();
            $conn->close();

            // Redirigir a view_reservation.php
            header("Location: view_reservation.php?user_name=" . urlencode($user_name));
            exit();
        }
        ?>
    </div>
</body>
</html>
