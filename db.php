<?php

require_once '../constant.php';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); 

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}
