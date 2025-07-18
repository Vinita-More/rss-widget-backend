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

$data = json_decode(file_get_contents("php://input"), true);

// Debug: Log the incoming data
error_log("Update widget data: " . json_encode($data));

$id = $data['id'] ?? null;
$widgetName = $data['widgetName'] ?? '';
$folder_id = $data['folder_id'] ?? 0;
$feed_url = isset($data["feed_url"]) ? $data["feed_url"] : null;

if (!$id) {
    echo json_encode(["error" => "ID is required"]);
    exit;
}

if (empty($widgetName)) {
    echo json_encode(["error" => "Widget name is required"]);
    exit;
}

// Use the settings data as provided by the frontend
//$set_data = isset($data['settings']) ? $data['settings'] : json_encode($data);

// First check if the widget exists and belongs to the user
$checkStmt = $conn->prepare("SELECT COUNT(*) FROM settings WHERE id = ? AND email = ?");
$checkStmt->bind_param("is", $id, $email);
$checkStmt->execute();
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count == 0) {
    echo json_encode(["error" => "Widget not found or unauthorized"]);
    exit;
}

// Check if widget name already exists for another widget of this user
$nameCheckStmt = $conn->prepare("SELECT COUNT(*) FROM settings WHERE widget_name = ? AND email = ? AND id != ?");
$nameCheckStmt->bind_param("ssi", $widgetName, $email, $id);
$nameCheckStmt->execute();
$nameCheckStmt->bind_result($nameCount);
$nameCheckStmt->fetch();
$nameCheckStmt->close();

if ($nameCount > 0) {
    echo json_encode(["error" => "Widget name already exists. Please choose another name."]);
    exit;
}

// Handle set_data properly
if (isset($data['settings'])) {
    // Parse the settings JSON
    $settings = json_decode($data['settings'], true) ?: [];
    
    // Ensure widgetName in settings matches the main widgetName
    $settings['widgetName'] = $widgetName;
    
    // Re-encode the settings
    $set_data = json_encode($settings);
} else {
    // If no settings provided, create minimal settings with widgetName
    $set_data = json_encode(['widgetName' => $widgetName]);
}

// Update the widget
$stmt = $conn->prepare("UPDATE settings SET widget_name = ?, folder_id = ?, feed_url = ?, set_data = ? WHERE id = ? AND email = ?");
if (!$stmt) {
    echo json_encode(["error" => "Prepare failed", "details" => $conn->error]);
    exit;
}

$stmt->bind_param("sissis", $widgetName, $folder_id, $feed_url, $set_data, $id, $email);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Widget updated successfully"]);
} else {
    echo json_encode(["error" => "Failed to update widget", "details" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>