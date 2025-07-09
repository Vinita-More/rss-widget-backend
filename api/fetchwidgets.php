<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once '../db.php';

// include_once('../constant.php');

// // Connect DB
// $conn = new mysqli($db_host, $db_user, $db_pass, $db_name); 
// if ($conn->connect_error) {
//     die(json_encode(["error" => $conn->connect_error]));
// }

// Read POST body
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['email'])) {
    echo json_encode(["error" => "Email is required"]);
    exit;
}

$email = $data['email'];

$stmt = $conn->prepare("SELECT id, widget_name FROM settings WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$widgets = [];
while ($row = $result->fetch_assoc()) {
    $widgets[] = $row;
}

echo json_encode($widgets);

$stmt->close();
$conn->close();
?>
