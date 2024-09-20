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

// Verificar que se hayan enviado los datos mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Registro de usuario
    if ($_POST['action'] == 'register') {
        $user = $_POST['username'];
        $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $email = $_POST['email'];
        $sql = "INSERT INTO Users (username, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $user, $pass, $email);

        if ($stmt->execute()) {
            header("Location: login.html"); // Redirigir al inicio de sesión
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // Inicio de sesión
    if ($_POST['action'] == 'login') {
        $user = $_POST['username'];
        $pass = $_POST['password'];
        $sql = "SELECT user_id, username, password FROM Users WHERE username=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($pass, $row['password'])) {
                $_SESSION['user_id'] = $row['user_id'];  // Guarda el ID del usuario en la sesión
                $_SESSION['user_name'] = $row['username'];  // Guarda el nombre del usuario en la sesión
                header("Location: search.php");  // Redirigir a la búsqueda de vuelos
                exit();
            } else {
                echo "Credenciales inválidas";
            }
        } else {
            echo "No existe ese usuario";
        }
    }
}

$conn->close();
?>
