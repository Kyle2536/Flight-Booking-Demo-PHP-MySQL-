<?php
session_start(); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$conn = new mysqli("localhost", "root", "", "test");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$flights_query = "SELECT flight_id, departure_airport, arrival_airport, departure_time FROM Flight";
$flights_result = $conn->query($flights_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Selection</title>
</head>
<body>
    <h1>Available Flights</h1>
    <table border="1">
        <tr>
            <th>Flight ID</th>
            <th>Origin</th>
            <th>Destination</th>
            <th>Departure Time</th>
        </tr>
        <?php
        if ($flights_result->num_rows > 0) {
            while ($row = $flights_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['flight_id'] . "</td>";
                echo "<td>" . $row['departure_airport'] . "</td>";
                echo "<td>" . $row['arrival_airport'] . "</td>";
                echo "<td>" . $row['departure_time'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No flights available</td></tr>";
        }
        ?>
    </table>
    <p><a href="dashboard.php">Dashboard</a></p>
    <h2>Change Your Flight</h2>
    <form action="flightselection.php" method="POST">
        <label for="passenger_id">Passenger ID:</label>
        <input type="text" id="passenger_id" name="passenger_id" required><br><br>

        <label for="password">Password:</label>
        <input type="text" id="password" name="password" required><br><br>

        <label for="flight_id">New Flight ID:</label>
        <input type="text" id="flight_id" name="flight_id" required><br><br>

        <button type="submit">Change Flight</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $passenger_id = trim($_POST['passenger_id']);
        $password = trim($_POST['password']);
        $new_flight_id = trim($_POST['flight_id']);

        if (empty($passenger_id) || empty($password) || empty($new_flight_id)) {
            echo "<p style='color:red;'>All fields are required.</p>";
        } else {
            $update_query = "UPDATE Passenger SET flight_id = '$new_flight_id' WHERE passenger_id = '$passenger_id' AND id_password = '$password'";

            if ($conn->query($update_query) === TRUE) {
                if ($conn->affected_rows > 0) {
                    echo "<p style='color:green;'>Flight updated successfully!</p>";
                } else {
                    echo "<p style='color:red;'>Invalid passenger ID or password, or failed to update flight.</p>";
                }
            } else {
                echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
            }
        }
    }

    $conn->close();
    ?>
</body>
</html>
