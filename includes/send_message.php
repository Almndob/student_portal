<?php
session_start();
require_once '../includes/db_config.php';
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$message_text = isset($_POST['message_text']) ? sanitize($_POST['message_text']) : '';

if ($receiver_id <= 0 || $message_text === '') {
    echo json_encode(['success' => false, 'message' => 'Please select a contact and enter a message.']);
    exit;
}

$insert_query = "INSERT INTO messages (sender_id, receiver_id, message, created_at)
                 VALUES (?, ?, ?, NOW())";
$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bind_param("iis", $user_id, $receiver_id, $message_text);

if ($insert_stmt->execute()) {
    createNotification(
        $receiver_id,
        'New Message',
        "You have a new message from $full_name",
        'message',
        $user_id,
        $conn
    );

    echo json_encode([
        'success' => true,
        'message_data' => [
            'id' => $conn->insert_id,
            'sender_id' => $user_id,
            'receiver_id' => $receiver_id,
            'message' => $message_text,
            'created_at' => date('Y-m-d H:i:s'),
            'is_sent' => true
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: '.$conn->error]);
}
