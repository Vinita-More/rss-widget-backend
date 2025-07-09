<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
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

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "No data received"]);
    exit;
}

// Validate email first
$email = isset($data['email']) ? $data['email'] : null;
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid or missing user email"]);
    exit;
}

// Validate widget name
if (!isset($data['widgetName']) || trim($data['widgetName']) === "") {
    http_response_code(400);
    echo json_encode(["error" => "Widget name cannot be empty"]);
    exit;
}
$widgetName = $data['widgetName'];

// Check if widget name exists for this user
$checkStmt = $conn->prepare("SELECT COUNT(*) FROM settings WHERE widget_name = ? AND email = ?");
$checkStmt->bind_param("ss", $widgetName, $email);
$checkStmt->execute();  
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count > 0) {
    echo json_encode(["error" => "Widget name already exists. Please choose another name."]);
    exit;
}

// Insert widget
$stmt = $conn->prepare("INSERT INTO settings (widget_name, width_mode, width, height_mode, height, autoscroll, font_style, border, border_color, text_alignment, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    "sssssssssss",
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
    $email
);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Settings saved"]);
} else {
    echo json_encode(["error" => "Insert failed", "details" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
