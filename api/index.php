<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once '../constant.php';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); 

if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}

$sql = "SELECT id, title, description, image, feedurl FROM dummyfeeddata";
$result = $conn->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
