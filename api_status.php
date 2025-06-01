<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'common.php';
if (!isset($_GET['number'])) {
    http_response_code(400);
    echo json_encode(['error' => 'number parameter required']);
    exit;
}
$number = preg_replace('/[^0-9]/', '', $_GET['number']);
$stmt = $db->prepare("SELECT * FROM filings WHERE number = :number LIMIT 1");
$stmt->bindValue(':number', $number, SQLITE3_TEXT);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
if ($result) {
    echo json_encode($result);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'not found']);
}
?>
