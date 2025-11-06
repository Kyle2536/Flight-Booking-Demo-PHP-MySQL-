# Flight Booking Demo (PHP + MySQL)

A minimal flight-booking web app that demonstrates **user auth**, **flight selection**, and **boarding pass** generation using **PHP** and **MySQL**.

> Pages included: `login.html`, `login.php`, `signup.php`, `dashboard.php`, `flightselection.php`, `boardingpass.php`.

---

## ✨ Features
- Passenger **sign‑up** and **login** (session‑based auth)
- Authenticated **dashboard** with user context
- **Flight selection** → creates a booking
- **Boarding pass** page for printing
- Clear, beginner‑friendly PHP code and SQL schema

---

## 🧱 Tech Stack
- PHP 8.x (works on 7.4+, but prefer 8.x)
- MySQL 8.x (or MariaDB)
- HTML/CSS (vanilla)
- Web server (Apache/Nginx) or PHP’s built‑in server

---

## 📁 Project Structure
```
.
├── login.html              # Login form (posts to login.php, links to signup.php)
├── login.php               # Handles login (validate, start session)
├── signup.php              # Create account (hash password, insert passenger)
├── dashboard.php           # Authenticated landing page
├── flightselection.php     # Browse/select flights, create booking
└── boardingpass.php        # Render a boarding pass for a booking
```

---

## 🚀 Getting Started (Local)

### 1) Prerequisites
Install:
- PHP 8.x (with PDO extension)
- MySQL server
- (Optional) Apache/Nginx; otherwise use PHP’s built‑in server

### 2) Clone
```bash
git clone https://github.com/<your-username>/<your-repo>.git
cd <your-repo>
```

### 3) Database Setup
Create a database, user, and tables.

```sql
CREATE DATABASE flight_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'flight_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON flight_demo.* TO 'flight_user'@'localhost';
FLUSH PRIVILEGES;

USE flight_demo;

-- Passengers
CREATE TABLE passengers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  passenger_id VARCHAR(128) UNIQUE NOT NULL, -- email or username
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Flights
CREATE TABLE flights (
  id INT AUTO_INCREMENT PRIMARY KEY,
  flight_no VARCHAR(16) NOT NULL,
  origin VARCHAR(64) NOT NULL,
  destination VARCHAR(64) NOT NULL,
  dep_time DATETIME NOT NULL,
  arr_time DATETIME NOT NULL,
  seats INT NOT NULL DEFAULT 100
);

-- Bookings
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  passenger_id INT NOT NULL,
  flight_id INT NOT NULL,
  booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  seat_no VARCHAR(8),
  CONSTRAINT fk_bookings_passenger FOREIGN KEY (passenger_id) REFERENCES passengers(id),
  CONSTRAINT fk_bookings_flight FOREIGN KEY (flight_id) REFERENCES flights(id)
);
```

*(Optional) seed sample flights:*
```sql
INSERT INTO flights (flight_no, origin, destination, dep_time, arr_time, seats) VALUES
('TT101', 'LBB', 'DFW', '2025-11-06 08:00:00', '2025-11-06 09:30:00', 120),
('TT202', 'DFW', 'LAX', '2025-11-06 11:00:00', '2025-11-06 13:30:00', 150);
```

### 4) Configure DB Connection
Create a small `config.php` (or add this block to each PHP file that queries the DB):

```php
<?php
$dsn = 'mysql:host=127.0.0.1;dbname=flight_demo;charset=utf8mb4';
$db_user = 'flight_user';
$db_pass = 'strong_password_here';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    exit('DB connection failed: ' . $e->getMessage());
}
```
> Then `require_once 'config.php';` at the top of your PHP files.

### 5) Run the App
Using PHP’s built‑in server:
```bash
php -S localhost:8000
```
Now open:
- http://localhost:8000/login.html → log in or go to sign‑up
- After login: `dashboard.php` → browse/choose flights
- Confirm to create a booking → view `boardingpass.php`

---

## 🔐 Security Checklist
- Use `password_hash()` and `password_verify()` for passwords
- Always use PDO **prepared statements** to prevent SQL injection
- Validate/sanitize all user input server‑side
- Regenerate session ID after login: `session_regenerate_id(true)`
- Restrict booking/boarding‑pass routes to authenticated users

---

## 🔧 Troubleshooting
- **“DB connection failed”** → verify `config.php` credentials and MySQL is running
- **Blank page / warnings hidden** → enable errors during dev:
  ```php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  ```
- **404 on PHP pages** → ensure you’re serving from the project directory (where files live)

---

## 🗺️ Roadmap Ideas
- Client‑side validation and nicer error messages
- Seat map + seat inventory decrement
- “My bookings” page + cancellation
- Admin pages for adding flights
- Styling with Bootstrap or Tailwind

---

## 📜 License
Choose a license (MIT recommended for demos) and add a `LICENSE` file.
