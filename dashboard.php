<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['passenger_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "test");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$passenger_id = $_SESSION['passenger_id'];

$sql = "
    SELECT p.first_name, p.last_name, f.flight_id, f.departure_airport, f.departure_gate, f.departure_time, 
           f.arrival_airport, f.arrival_gate, f.arrival_time, f.airline_id 
    FROM Passenger p
    JOIN Flight f ON p.flight_id = f.flight_id
    WHERE p.passenger_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $passenger_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $passenger_name = $row['first_name'] . ' ' . $row['last_name'];
    $flight_number = $row['flight_id'];
    $departure_city = $row['departure_airport'];
    $departure_time = $row['departure_time'];
    $departure_gate = $row['departure_gate'];
    $arrival_city = $row['arrival_airport'];
    $arrival_time = $row['arrival_time'];
    $arrival_gate = $row['arrival_gate'];
    $airline = $row['airline_id'];

    // Output the dashboard with the flight details
    echo "<h1>Welcome, $passenger_name</h1>";
    echo "<div id='flight-info'>
            <h2>Your Flight Details</h2>
            <p>Flight Number: $flight_number</p>
            <p>Departure: $departure_city at $departure_time</p>
            <p>Departure Gate: $departure_gate</p>
            <p>Arrival: $arrival_city at $arrival_time</p>
            <p>Arrival Gate: $arrival_gate</p>
            <p>Airline: $airline</p>
            <p><a href='boardingpass.php'>Boarding Pass</a></p>
            <p><a href='flightselection.php'>Change Flight</a></p>
            <p><a href='login.html'>Log out</a></p>
          </div>";
} else {
    echo "<h2>No current flights booked.</h2>";
    echo "<p><a href='flightselection.php'>Book Flight</a></p>";
}

$stmt->close();
$conn->close();
?>