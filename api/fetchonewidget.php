<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once '../db.php';

// require_once '../constant.php';

// $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
// if ($conn->connect_error) {
//     die(json_encode(["error" => $conn->connect_error]));
// }
require_once '../vendor/autoload.php';
require_once 'auth_middleware.php';

$user = authenticate(); // Returns decoded token
$email = $user->email;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(["error" => "No ID provided"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM settings WHERE id = ? AND email = ?");
$stmt->bind_param("is", $id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($widget = $result->fetch_assoc()) {
    
    echo json_encode($widget);
} else {
    echo json_encode(["error" => "Widget not found"]);
}

$stmt->close();
$conn->close();
?>
