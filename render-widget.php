<!-- </*?php
require_once '../db.php'; // this should create a $conn = new mysqli(...)

/*$widget_id = $_GET['widget_id'] ?? '';
if (!$widget_id) {
    echo "Invalid widget.";
    exit;
}

// Prepare statement
/*$stmt = $conn->prepare("SELECT * FROM settings WHERE id = ?");
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
    font-family: <//?= htmlspecialchars($settings['font_style'] ?? 'Arial') ?>;
    font-size: <//?= htmlspecialchars($settings['font_size'] ?? '16px') ?>;
    text-align: <//?= htmlspecialchars($settings['text_alignment'] ?? 'left') ?>;
    <//?php if ($settings['border'] === "true"): ?>
    border: 2px solid <//?= htmlspecialchars($settings['border_color'] ?? '#000') ?>;
    padding: 10px;
    <//?php endif; ?>
  }
  </style>
</head>
<body>
  <h3><//?= htmlspecialchars($settings['widget_name']) ?></h3>
  <p>This is your local RSS widget rendering with user settings.</p>
</body>
</html> -->
<?php
require_once './db.php';

$widget_id = $_GET['widget_id'] ?? '';

if (!$widget_id) {
    echo "Widget ID is required.";
    exit;
}

// Fetch the widget settings
$sql = "SELECT set_data FROM settings WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $widget_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Widget not found.";
    exit;
}

$row = $result->fetch_assoc();
$settings = json_decode($row['set_data'], true);

// Apply the settings to render your widget
echo "<div style='font-family: {$settings['font']}; color: {$settings['textColor']};'>";
echo "<h4>{$settings['widgetName']}</h4>";
echo "<p>Widget ID: $widget_id</p>";
// Include RSS rendering logic here
echo "</div>";
?>
