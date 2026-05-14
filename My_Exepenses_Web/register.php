<?php
$servername = "localhost";
$username = "root";   // XAMPP default
$password = "";       // XAMPP default
$dbname = "expenses_app";  // apna DB name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents("php://input"), true);
if (!$input) { 
    $input = $_POST; 
}

$name = $input['name'] ?? '';
$mobile = $input['mobile'] ?? '';
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';
$address = $input['address'] ?? '';

// Required fields check
if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Name, email, and password are required"]);
    exit();
}

// Check if email already exists
$check_sql = "SELECT id FROM users WHERE email = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already exists"]);
    exit();
}

// Insert new user (using prepared statement properly)
$stmt = $conn->prepare("INSERT INTO users (name, mobile, email, password, address) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $mobile, $email, $password, $address);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "User registered successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "DB Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
