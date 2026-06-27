<?php
/**
 * Helper Functions for Student Portal
 */

//session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /student_portal/index.php");
        exit();
    }
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Get user info from database
function getUserInfo($user_id, $conn) {
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get student info
function getStudentInfo($student_id, $conn) {
    $query = "SELECT s.*, u.full_name, u.email FROM students s 
              JOIN users u ON s.user_id = u.id 
              WHERE s.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get student notes
function getStudentNotes($student_id, $conn, $limit = 10) {
    $query = "SELECT n.*, u.full_name as teacher_name FROM notes n 
              JOIN users u ON n.teacher_id = u.id 
              WHERE n.student_id = ? 
              ORDER BY n.created_at DESC 
              LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $student_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $notes = [];
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }
    return $notes;
}

// Get student grades
function getStudentGrades($student_id, $conn) {
    $query = "SELECT * FROM grades WHERE student_id = ? ORDER BY date_recorded DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    return $grades;
}

// Get student attendance
function getStudentAttendance($student_id, $conn) {
    $query = "SELECT * FROM attendance WHERE student_id = ? ORDER BY attendance_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance = [];
    while ($row = $result->fetch_assoc()) {
        $attendance[] = $row;
    }
    return $attendance;
}

// Get unread notifications count
function getUnreadNotificationsCount($user_id, $conn) {
    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Get notifications
function getNotifications($user_id, $conn, $limit = 10) {
    $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    return $notifications;
}

// Get messages between two users
function getMessages($user1_id, $user2_id, $conn) {
    $query = "SELECT * FROM messages 
              WHERE (sender_id = ? AND receiver_id = ?) 
              OR (sender_id = ? AND receiver_id = ?)
              ORDER BY created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $user1_id, $user2_id, $user2_id, $user1_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    return $messages;
}

// Create notification
function createNotification($user_id, $title, $message, $type, $related_id, $conn) {
    $query = "INSERT INTO notifications (user_id, title, message, type, related_id) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssi", $user_id, $title, $message, $type, $related_id);
    return $stmt->execute();
}

// Get teacher's students
function getTeacherStudents($teacher_id, $conn) {
    $query = "SELECT DISTINCT s.*, u.full_name, u.email FROM students s 
              JOIN users u ON s.user_id = u.id 
              WHERE s.class_name IN (
                  SELECT class_assigned FROM teachers WHERE user_id = ?
              )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    return $students;
}

// Format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    return date('M d, Y H:i', strtotime($datetime));
}

?>

