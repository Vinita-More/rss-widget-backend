<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once '../db.php';


// Get folder_id from query string (default to 0 if not provided)$feedUrl = $input['feed_url'];

libxml_use_internal_errors(true);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $input = json_decode(file_get_contents("php://input"), true);


    if (isset($input['feed_url']) && !empty($input['feed_url'])) {
        $feedUrl = $input['feed_url'];


    // Fetch content using cURL (supports redirects and HTTPS)
    $ch = curl_init($feedUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0"); // simulate browser

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 🔓 disables certificate check
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(["error" => "cURL error: " . curl_error($ch)]);
        curl_close($ch);
        exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(["error" => "HTTP error: $httpCode"]);
    exit;
}

// Parse the XML content
$rss = simplexml_load_string($response);

if (!$rss) {
    echo json_encode(["error" => "Invalid or malformed RSS/Atom XML"]);
    exit;
}

        $items = [];

        if (isset($rss->channel)) {
            // RSS 2.0
             
            foreach ($rss->channel->item as $entry) {
                                $image = null;

                $media = $entry->children('media', true);
                if ($media->thumbnail) {
                    $image = (string)$media->thumbnail->attributes()->url;
                } elseif ($media->content) {
                    $image = (string)$media->content->attributes()->url;
                }

                // Fallback: try to extract <img> from description
                if (!$image && isset($entry->description)) {
                    preg_match('/<img[^>]+src="([^">]+)"/i', (string)$entry->description, $matches);
                    if (!empty($matches[1])) {
                        $image = $matches[1];
                    }
                }
                $items[] = [
                    "title" => (string)$entry->title,
                    "description" => (string)$entry->description,
                    "feedurl" => (string)$entry->link,
                    "pubDate" => (string)$entry->pubDate,
                     "image" => $image,
                ];
            }
        } elseif (isset($rss->entry)) {
            // Atom
            foreach ($rss->entry as $entry) {
                 $image = null;

                $media = $entry->children('media', true);
                if ($media->thumbnail) {
                    $image = (string)$media->thumbnail->attributes()->url;
                } elseif ($media->content) {
                    $image = (string)$media->content->attributes()->url;
                }

                // Fallback: check <summary> or <content>
                if (!$image && isset($entry->summary)) {
                    preg_match('/<img[^>]+src="([^">]+)"/i', (string)$entry->summary, $matches);
                    if (!empty($matches[1])) {
                        $image = $matches[1];
                    }
                }
                $items[] = [
                    "title" => (string)$entry->title,
                    "description" => (string)$entry->summary,
                    "feedurl" => (string)$entry->link['href'],
                    "pubDate" => (string)$entry->updated,
                    "image" => $image,
                ];
            }
        } else {
            echo json_encode(["error" => "Unsupported feed format"]);
            exit;
        }

        echo json_encode(["items" => $items]);
        exit;
    }
}

// Handle folder feed via GET
$folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;

try {
    if ($folder_id > 0) {
        $stmt = $conn->prepare("SELECT id, title, description, image, feedurl FROM dummyfeeddata WHERE folder_id = ?");
        $stmt->bind_param("i", $folder_id);
    } else {
        $stmt = $conn->prepare("SELECT id, title, description, image, feedurl FROM dummyfeeddata");
    }

    if (!$stmt) {
        echo json_encode(["error" => "Database prepare failed"]);
        exit;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
}

$conn->close();
