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


/**/
if (!isset($data['widgetName']) || trim($data['widgetName']) === "") {
    http_response_code(400);
    echo json_encode(["error" => "Widget name cannot be empty"]);
    exit;
}
/**/

// Check if widget_name already exists
$widgetName = $data['widgetName'];

$checkStmt = $conn->prepare("SELECT COUNT(*) FROM settings WHERE widget_name = ?");
$checkStmt->bind_param("s", $widgetName);
$checkStmt->execute();
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count > 0) {
    echo json_encode(["error" => "Widget name already exists. Please choose another name."]);
    exit;
}
/**/    

$stmt = $conn->prepare("INSERT INTO settings (widget_name, width_mode, width, height_mode, height, autoscroll, font_style, border, border_color, text_alignment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)");

$stmt->bind_param(
    "ssssssssss",
    $data['widgetName'],
    $data['widthMode'],
    $data['width'],
    $data['heightMode'],
    $data['height'],
    $data['autoscroll'],
    $data['fontStyle'],
    $data['border'],    
    $data['borderColor'],
    $data['textAlign']
);
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Settings saved"]);
} else {
    echo json_encode(["error" => "Insert failed", "details" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
