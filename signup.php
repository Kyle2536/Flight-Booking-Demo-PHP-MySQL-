<?php
$host = 'localhost';
$dbname = 'test';
$username = 'root';
$password = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $date_of_birth = $_POST['date_of_birth'];
        $flight_id = $_POST['flight_id'];
        $id_password = $_POST['id_password'];
        $class = $_POST['class'];

        $class_seats_map = [
            'First' => ['capacity_column' => 'first_capacity', 'board_group' => 1],
            'Business' => ['capacity_column' => 'business_capacity', 'board_group' => 2],
            'Economy' => ['capacity_column' => 'economy_capacity', 'board_group' => 3]
        ];
        $capacity_column = $class_seats_map[$class]['capacity_column'];
        $board_group = $class_seats_map[$class]['board_group'];

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT $capacity_column FROM Flight WHERE flight_id = :flight_id FOR UPDATE");
        $stmt->bindParam(':flight_id', $flight_id);
        $stmt->execute();
        $seat_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$seat_data) {
            throw new Exception("Flight not found.");
        }

        $available_seats = $seat_data[$capacity_column];
        if ($available_seats <= 0) {
            throw new Exception("No available seats in $class class for this flight.");
        }

        do {
            $passenger_id = random_int(10000000, 99999999);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Passenger WHERE passenger_id = :passenger_id");
            $stmt->bindParam(':passenger_id', $passenger_id);
            $stmt->execute();
            $idExists = $stmt->fetchColumn() > 0;
        } while ($idExists);

        $stmt = $pdo->prepare("
            SELECT MAX(CAST(SUBSTRING(boarding_pass_id, LENGTH(:flight_id) + 1) AS UNSIGNED)) AS max_suffix
            FROM Boarding_Pass, Flight
            WHERE flight_id = :flight_id
        ");
        $stmt->bindParam(':flight_id', $flight_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $max_suffix = $result['max_suffix'] ?? 1110;
        $boarding_pass_id = $flight_id . ($max_suffix + 1);

        $stmt = $pdo->prepare("
            INSERT INTO Passenger (first_name, last_name, date_of_birth, passenger_id, flight_id, boarding_pass_id, id_password)
            VALUES (:first_name, :last_name, :date_of_birth, :passenger_id, :flight_id, :boarding_pass_id, :id_password)
        ");
        $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':date_of_birth' => $date_of_birth,
            ':passenger_id' => $passenger_id,
            ':flight_id' => $flight_id,
            ':boarding_pass_id' => $boarding_pass_id,
            ':id_password' => $id_password
        ]);

        $stmt = $pdo->prepare("
            INSERT INTO Boarding_Pass (boarding_pass_id, passenger_id, class, board_group)
            VALUES (:boarding_pass_id, :passenger_id, :class, :board_group)
        ");
        $stmt->execute([
            ':boarding_pass_id' => $boarding_pass_id,
            ':passenger_id' => $passenger_id,
            ':class' => $class,
            ':board_group' => $board_group
        ]);

        $pdo->commit();

        echo "Account created successfully! Your Passenger ID: $passenger_id, Boarding Pass ID: $boarding_pass_id.";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Sign Up</title>
</head>
<body>
    <h1>Passenger Sign Up</h1>
    <form method="POST">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" required><br>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" required><br>

        <label for="date_of_birth">Date of Birth:</label>
        <input type="date" name="date_of_birth" required><br>

        <label for="flight_id">Select a Flight:</label>
        <select id="flight_id" name="flight_id" required>
            <?php
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $pdo->query("SELECT flight_id, departure_airport, arrival_airport FROM Flight");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . htmlspecialchars($row['flight_id']) . "'>Flight " . htmlspecialchars($row['flight_id']) . " (" . htmlspecialchars($row['departure_airport']) . " -> " . htmlspecialchars($row['arrival_airport']) . ")</option>";
                }
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
            ?>
        </select><br>

        <label for="class">Select Seat Class:</label>
        <select id="class" name="class" required>
            <option value="First">First</option>
            <option value="Business">Business</option>
            <option value="Economy">Economy</option>
        </select><br>

        <label for="id_password">Password:</label>
        <input type="password" name="id_password" required><br>

        <button type="submit">Sign Up</button>

	<p><a href='login.html'>Return to Log in</a></p>

    </form>
</body>
</html>