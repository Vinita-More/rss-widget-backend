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
$widgetId = $data['id'] ?? null;

if (!$widgetId) {
    echo json_encode(["error" => "Widget ID is required"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM settings WHERE id = ?");
$stmt->bind_param("i", $widgetId);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget deleted"]);
} else {
    echo json_encode(["error" => "Failed to delete widget"]);
}

$stmt->close();
$conn->close();
?>
