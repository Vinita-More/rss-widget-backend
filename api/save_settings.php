<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../constant.php';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); 

if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}

// Read POST data from fetch()
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "No data received"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO settings (width_mode, width, height_mode, height, autoscroll, open_links, font_style, border, border_color) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sssssssss",
    $data['widthMode'],
    $data['width'],
    $data['heightMode'],
    $data['height'],
    $data['autoscroll'],
    $data['openLinks'],
    $data['fontStyle'],
    $data['border'],
    $data['borderColor']
);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Settings saved"]);
} else {
    echo json_encode(["error" => "Insert failed", "details" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
