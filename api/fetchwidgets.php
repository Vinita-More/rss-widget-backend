<?php
header("Access-Control-Allow-Origin: *"); // Use specific origin, not "*"
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db.php';
require_once 'auth_middleware.php';

$user = authenticate(); // returns decoded JWT object
$email = $user->email;
// include_once('../constant.php');

// // Connect DB
// $conn = new mysqli($db_host, $db_user, $db_pass, $db_name); 
// if ($conn->connect_error) {
//     die(json_encode(["error" => $conn->connect_error]));
// }

// Read POST body
// $data = json_decode(file_get_contents("php://input"), true);
// if (!isset($data['email'])) {
//     echo json_encode(["error" => "Email is required"]);
//     exit;
// }

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
