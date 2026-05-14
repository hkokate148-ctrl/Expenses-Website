<?php
$servername = "localhost";
$username = "root";    // XAMPP default
$password = "";        // XAMPP default
$dbname = "expenses_app"; // Same DB as register.php

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);
if (!$input) { $input = $_POST; }

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit();
}

// Check user exists
$stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    exit();
}

$user = $result->fetch_assoc();

// Check password (plain text as in registration)
if ($password === $user['password']) {
    echo json_encode(["success" => true, "message" => "Login successful. Welcome, " . $user['name']]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
}

$stmt->close();
$conn->close();
?>
