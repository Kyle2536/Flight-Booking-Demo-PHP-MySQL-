<?php
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
    SELECT bp.boarding_pass_id, bp.class, bp.board_group, 
           f.flight_id, f.departure_time, f.departure_gate, f.arrival_gate, f.airline_id
    FROM Boarding_Pass bp
    JOIN Passenger p ON bp.passenger_id = p.passenger_id
    JOIN Flight f ON p.flight_id = f.flight_id
    WHERE p.passenger_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $passenger_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $boarding_pass_id = $row['boarding_pass_id'];
    $class = $row['class'];
    $board_group = $row['board_group'];
    $flight_id = $row['flight_id'];
    $departure_time = $row['departure_time'];
    $departure_gate = $row['departure_gate'];
    $arrival_gate = $row['arrival_gate'];
    $airline = $row['airline_id'];

    $luggage_stmt = $conn->prepare("SELECT luggage_id, weight FROM Luggage WHERE passenger_id = ?");
    $luggage_stmt->bind_param("i", $passenger_id);
    $luggage_stmt->execute();
    $luggage_result = $luggage_stmt->get_result();

    echo "
    <body>
        <h1>Hello Passenger ID: $passenger_id</h1>
        <h2>Your Boarding Pass Details</h2>
        <p>Boarding Pass ID: $boarding_pass_id</p>
        <p>Class: $class</p>
        <p>Boarding Group #: $board_group</p>
        <p>Flight ID: $flight_id</p>
        <p>Departure: Gate $departure_gate at $departure_time</p>
        <p>Arrival: Gate $arrival_gate</p>
        <p>Airline: $airline</p>";

    if ($luggage_result->num_rows > 0) {
        echo "<h2>Your Luggage Details</h2>";
        while ($luggage_row = $luggage_result->fetch_assoc()) {
            echo "<p>Luggage ID: " . $luggage_row['luggage_id'] . ", Weight: " . $luggage_row['weight'] . " lbs</p>";
        }
    } else {
        echo "<p>You currently have no luggage added.</p>";
    }

    $luggage_count = $luggage_result->num_rows;
    if ($luggage_count < 3) {
        echo "
        <h2>Add Luggage</h2>
        <form action='' method='POST'>
            <label for='weight'>Luggage Weight (lbs):</label>
            <input type='number' step='0.1' name='weight' id='weight' required>
            <button type='submit'>Add Luggage</button>
        </form>";
    } else {
        echo "<p>You have already added the maximum of 3 luggage items.</p>";
    }

    echo "</body>";
} else {
    echo "<h1>No boarding pass details found.</h1>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weight = (float) $_POST['weight'];

    $check_stmt = $conn->prepare("SELECT COUNT(*) AS luggage_count FROM Luggage WHERE passenger_id = ?");
    $check_stmt->bind_param("i", $passenger_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    $luggage_count = $check_row['luggage_count'];

    if ($weight > 50) {
        echo "<p>Error: Luggage weight cannot exceed 50lbs.</p>";
    } elseif ($luggage_count < 3) {
        $insert_stmt = $conn->prepare("INSERT INTO Luggage (passenger_id, weight) VALUES (?, ?)");
        $insert_stmt->bind_param("id", $passenger_id, $weight);
        if ($insert_stmt->execute()) {
            echo "<p>Luggage added successfully!</p>";
            // Refresh page to show updated luggage details
            header("Refresh:0");
        } else {
            echo "<p>Error adding luggage: " . $insert_stmt->error . "</p>";
        }
    } else {
        echo "<p>You cannot add more than 3 luggage items.</p>";
    }
}

    echo "<p><a href='dashboard.php'>Return To Dashboard</a></p>";

$stmt->close();
$conn->close();
?>