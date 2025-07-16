<?php
require_once '../db.php'; // this should create a $conn = new mysqli(...)

$widget_id = $_GET['widget_id'] ?? '';
if (!$widget_id) {
    echo "Invalid widget.";
    exit;
}

// Prepare statement
$stmt = $conn->prepare("SELECT * FROM settings WHERE id = ?");
$stmt->bind_param("s", $widget_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();

if (!$settings) {
    echo "Widget not found.";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <style>
      body {
    font-family: <?= htmlspecialchars($settings['font_style'] ?? 'Arial') ?>;
    font-size: <?= htmlspecialchars($settings['font_size'] ?? '16px') ?>;
    text-align: <?= htmlspecialchars($settings['text_alignment'] ?? 'left') ?>;
    <?php if ($settings['border'] === "true"): ?>
    border: 2px solid <?= htmlspecialchars($settings['border_color'] ?? '#000') ?>;
    padding: 10px;
    <?php endif; ?>
  }
  </style>
</head>
<body>
  <h3><?= htmlspecialchars($settings['widget_name']) ?></h3>
  <p>This is your local RSS widget rendering with user settings.</p>
</body>
</html>
