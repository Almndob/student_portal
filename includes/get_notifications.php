<?php
session_start();
require_once '../db_config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0, 'notifications' => []]);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

// استعلام حسب الدور
if ($role === 'teacher') {
    $query = $conn->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_role='teacher' AND is_read=0");
} else if ($role === 'parent') {
    $query = $conn->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_role='parent' AND is_read=0");
} else {
    echo json_encode(['count' => 0, 'notifications' => []]);
    exit;
}

$query->execute();
$result = $query->get_result()->fetch_assoc();

echo json_encode(['count' => $result['unread_count']]);
