<?php
require_once 'common.php';
$db = init_database();

if (!isset($_GET['filing_id'])) {
    die('Invalid request.');
}

$filing_id = $_GET['filing_id'];

$stmt = $db->prepare("SELECT * FROM filings WHERE id = :filing_id AND status = 'approved'");
$stmt->bindValue(':filing_id', $filing_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$filing = $result->fetchArray(SQLITE3_ASSOC);

if (!$filing) {
    die('Filing not found or not approved.');
}

// Certificate content
$filing_number = htmlspecialchars($filing['filing_number']);
$website_name = htmlspecialchars($filing['website_name']);
$website_url = htmlspecialchars($filing['website_url']);
$submission_date = htmlspecialchars($filing['submission_date']);

// Certificate dimensions
$width = 1000;
$height = 600;
$image = imagecreatetruecolor($width, $height);

// Define colors
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$gray = imagecolorallocate($image, 100, 100, 100);
$dark_blue = imagecolorallocate($image, 20, 30, 60);
$light_blue = imagecolorallocate($image, 60, 90, 150);
$accent_color = imagecolorallocate($image, 0, 150, 255); // A vibrant blue

// Create a gradient background
for ($i = 0; $i < $height; $i++) {
    $r = intval(20 + (60 - 20) * $i / $height);
    $g = intval(30 + (90 - 30) * $i / $height);
    $b = intval(60 + (150 - 60) * $i / $height);
    $line_color = imagecolorallocate($image, $r, $g, $b);
    imageline($image, 0, $i, $width, $i, $line_color);
}

// Add a subtle pattern or texture (optional)
// For simplicity, we'll skip complex patterns here, but you could draw lines, circles, etc.



$font_size_title = 5; // Max size for imagestring
$font_size_subtitle = 4;
$font_size_text = 3;
$font_size_small = 2;

// Add title
$title = "Website Filing Certificate";
$subtitle = "";

// imagestring($image, $font_size_title, ($width - imagefontwidth($font_size_title) * strlen($title)) / 2, 100, $title, $white); // Original title line commented out as previous search block was likely an outdated version of this line.
imagestring($image, $font_size_subtitle, ($width - imagefontwidth($font_size_subtitle) * strlen($subtitle)) / 2, 150, $subtitle, $accent_color);

// Add content with improved layout
$text_x = 100;
$text_y_start = 250;
$line_height = 40;

imagestring($image, $font_size_text, $text_x, $text_y_start, $filing_number, $white);
imagestring($image, $font_size_text, $text_x, $text_y_start + $line_height, "Website Name: " . $website_name, $white);
imagestring($image, $font_size_text, $text_x, $text_y_start + 2 * $line_height, "Website URL: " . $website_url, $white);
imagestring($image, $font_size_text, $text_x, $text_y_start + 3 * $line_height, "Submission Date: " . $submission_date, $white);

// Add a decorative line
imageline($image, $text_x, $text_y_start + 3 * $line_height + 20, $width - $text_x, $text_y_start + 3 * $line_height + 20, $accent_color);

// Add official seal/signature
$seal_text = "Alliance ICP Filing Center";
$date_text = date('Y-m-d');

imagestring($image, $font_size_text, $width - 350, $height - 150, $seal_text, $accent_color);
imagestring($image, $font_size_small, $width - 350, $height - 120, $date_text, $white);

// Add a QR code placeholder (requires a QR code library, not implemented here)
// For example, using a library like 'phpqrcode'
// QR code content could be the website URL or filing number
// imagettftext($image, $font_size_small, 0, $width - 200, $height - 80, $white, $font_path, "[QR Code Placeholder]");

// Add a subtle footer message
imagestring($image, $font_size_small, $text_x, $height - 50, "This certificate is for entertainment and display purposes only and has no legal effect.", $gray);

// Output image
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="icp_certificate_' . $filing['filing_number'] . '.png"');
imagepng($image);
imagedestroy($image);

?>