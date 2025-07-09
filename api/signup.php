<?php
// Enable CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once '../db.php';

// require_once '../constant.php'; 
// $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// if ($conn->connect_error) {
//     echo json_encode(["success" => false, "message" => "Database connection failed"]);
//     exit;
// }

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email']);
$password = $data['password'];

// Validate input
if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Missing email or password"]);
    exit;
}

// Check if user already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already exists"]);
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Signup failed"]);
}
