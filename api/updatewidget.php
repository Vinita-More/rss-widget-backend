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
require_once 'auth_middleware.php'; 
$user = authenticate(); 
$email = $user->email;

// require_once '../constant.php';

// $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
// if ($conn->connect_error) {
//     die(json_encode(["error" => $conn->connect_error]));
// }

$data = json_decode(file_get_contents("php://input"), true);
$feed_url = isset($data["feed_url"]) ? $data["feed_url"] : null;
$id = $data['id'] ?? null;
$folder_id = $data['folder_id'];
if (!$id) {
    echo json_encode(["error" => "ID is required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE settings SET widget_name=?, folder_id= ?,width_mode=?, width=?, height_mode=?, height=?, autoscroll=?, font_style=?, border=?, border_color=?, text_alignment=?, feed_url = ? WHERE id=? AND email=?");
$stmt->bind_param(
    "sissssssssssis",
    $data['widgetName'],
    $folder_id,
    $data['widthMode'],
    $data['width'],
    $data['heightMode'],
    $data['height'],
    $data['autoscroll'],
    $data['fontStyle'],
    $data['border'],
    $data['borderColor'],
    $data['textAlign'],
    $data['feed_url'],
    $id,
    $email,
     
);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget updated"]);
} else {
    echo json_encode(["error" => "Failed to update widget", "details" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
