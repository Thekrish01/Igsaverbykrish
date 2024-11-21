<?php
header("Content-Type: application/json"); // Set the content type to JSON

function fetchPageContent($url) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function extractInstagramMedia($html) {
    $media = [];

    // Extract video link (for Reels or posts with video)
    if (preg_match('/property="og:video" content="([^"]+)"/', $html, $matches)) {
        $media['video'] = $matches[1];
    }

    // Extract image link (for posts with images)
    if (preg_match('/property="og:image" content="([^"]+)"/', $html, $matches)) {
        $media['image'] = $matches[1];
    }

    return $media;
}

if (isset($_GET['link']) && filter_var($_GET['link'], FILTER_VALIDATE_URL)) {
    $link = $_GET['link'];

    // Validate it's an Instagram URL
    if (strpos($link, 'instagram.com') === false) {
        echo json_encode([
            "status" => "error",
            "message" => "Provided link is not a valid Instagram URL.",
        ]);
        exit;
    }

    // Fetch page content
    $pageContent = fetchPageContent($link);

    if ($pageContent) {
        // Extract media links
        $mediaLinks = extractInstagramMedia($pageContent);

        if (!empty($mediaLinks)) {
            // Return JSON response
            echo json_encode([
                "status" => "success",
                "media" => $mediaLinks,
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "No media found on the provided link.",
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to fetch content from the provided link.",
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid or missing 'link' parameter.",
    ]);
}
?>