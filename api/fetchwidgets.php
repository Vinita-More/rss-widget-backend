<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include_once('../constant.php');


$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); 

if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}

$result = $conn->query("SELECT id, widget_name FROM settings");

$widgets = [];

while ($row = $result->fetch_assoc()) {
    $widgets[] = $row;
}

echo json_encode($widgets);


$conn->close();
?>