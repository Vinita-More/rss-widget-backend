<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db.php';
require_once 'auth_middleware.php';
$user = authenticate(); 
$email = $user->email;

$data = json_decode(file_get_contents("php://input"), true);

//file_put_contents("debug_input.json", json_encode($data, JSON_PRETTY_PRINT)); 
if (!$data) {
    echo json_encode(["error" => "No data received"]);
    exit;
}

// If frontend sends a 'settings' field (JSON string), use it for set_data
if (isset($data['settings'])) {
    $set_data = $data['settings'];
} else {
    $set_data = json_encode($data);
}

//$folder_id = $data['folder_id'];

$folder_id = isset($data['folder_id']) ? $data['folder_id'] : 0;

// Validate widget name
if (!isset($data['widgetName']) || trim($data['widgetName']) === "") {
    http_response_code(400);
    echo json_encode(["error" => "Widget name cannot be empty"]);
    exit;
}
$feed_url = isset($data["feed_url"]) ? $data["feed_url"] : null;
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

$stmt = $conn->prepare("INSERT INTO settings (widget_name, folder_id, feed_url, set_data, email) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param(
    "sisss",
    $data['widgetName'],
    $data['folder_id'],
    $data['feed_url'],
    $set_data,
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


