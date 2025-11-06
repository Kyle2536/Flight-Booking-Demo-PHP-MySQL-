<?php
session_start();

$conn = new mysqli("localhost", "root", "", "test");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$passenger_id = (int) trim($_POST['passenger_id']);
$id_password = trim($_POST['id_password']);

$sql = "SELECT * FROM Passenger WHERE passenger_id = '$passenger_id' AND id_password = '$id_password'";

$stmt = $conn->prepare("SELECT * FROM Passenger WHERE passenger_id = ? AND id_password = ?");
$stmt->bind_param("is", $passenger_id, $id_password); // 'i' for integer, 's' for string

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $_SESSION['passenger_id'] = $passenger_id;
    
    header("Location: dashboard.php");
    exit();  // Always call exit after redirect to ensure no further code is executed
} else {
    echo "Invalid ID or password";
    echo "<p><a href='login.html'>Return To Log in</a></p>";

}

$stmt->close();
$conn->close();
?>