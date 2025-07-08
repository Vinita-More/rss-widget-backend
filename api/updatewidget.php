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
$widgetName = $data['widgetName'] ?? null;
$widthMode = $data['widthMode'] ?? null;
$width = $data['width'] ?? null;
$heightMode = $data['heightMode'] ?? null;
$height = $data['height'] ?? null;
$autoscroll = $data['autoscroll'] ?? null;
$fontStyle = $data['fontStyle'] ?? null;
$border = $data['border'] ?? null;
$borderColor = $data['borderColor'] ?? null;
$textAlign = $data['textAlign'] ?? null;

if (!$id || !$widgetName) {
    echo json_encode(["error" => "ID and widget name are required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE settings SET widget_name = ?, width_mode = ?, width = ?, height_mode = ?, height = ?, autoscroll = ?, font_style = ?, border = ?, border_color = ?, text_alignment = ? WHERE id = ?");
$stmt->bind_param("ssssssssssi", $widgetName, $widthMode, $width, $heightMode, $height, $autoscroll, $fontStyle, $border, $borderColor, $textAlign, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget updated"]);
} else {
    echo json_encode(["error" => "Update failed", "details" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
