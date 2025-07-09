<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once '../db.php';

// require_once '../constant.php';

// $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
// if ($conn->connect_error) {
//     die(json_encode(["error" => $conn->connect_error]));
// }

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
if (!$id) {
    echo json_encode(["error" => "ID is required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE settings SET widget_name=?, width_mode=?, width=?, height_mode=?, height=?, autoscroll=?, font_style=?, border=?, border_color=?, text_alignment=? WHERE id=?");
$stmt->bind_param(
    "ssssssssssi",
    $data['widgetName'],
    $data['widthMode'],
    $data['width'],
    $data['heightMode'],
    $data['height'],
    $data['autoscroll'],
    $data['fontStyle'],
    $data['border'],
    $data['borderColor'],
    $data['textAlign'],
    $id
);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget updated"]);
} else {
    echo json_encode(["error" => "Failed to update widget", "details" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
