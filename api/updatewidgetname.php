<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once '../db.php';
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


$data = json_decode(file_get_contents("php://input"));
$id = $data->id ?? null;
$widget_name = $data->widget_name ?? null;

if (!$id || !$widget_name) {
    echo json_encode(["error" => "Missing ID or name"]);
    exit;
}

$stmt = $conn->prepare("UPDATE settings SET widget_name = ? WHERE id = ?");
$stmt->bind_param("si", $widget_name, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to update name"]);
}?>
