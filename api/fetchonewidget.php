<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once '../constant.php';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(["error" => "No ID provided"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM settings WHERE id = ?");
$stmt->bind_param("i", $id);
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
