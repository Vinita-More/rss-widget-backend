<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once '../db.php';


// Get folder_id from query string (default to 0 if not provided)
$folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;

if ($folder_id > 0) {
    $stmt = $conn->prepare("SELECT id, title, description, image, feedurl FROM dummyfeeddata WHERE folder_id = ?");
    $stmt->bind_param("i", $folder_id);
} else {
    $stmt = $conn->prepare("SELECT id, title, description, image, feedurl FROM dummyfeeddata");
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

$conn->close();