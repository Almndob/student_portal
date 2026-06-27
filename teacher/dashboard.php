<?php
session_start();
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SESSION['role'] !== 'teacher') {
    header("Location: /student_portal/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Get teacher's students
$students = getTeacherStudents($user_id, $conn);

// Get recent notes added by this teacher
$notes_query = "SELECT n.*, s.id as student_id, u.full_name as student_name FROM notes n 
                JOIN students s ON n.student_id = s.id 
                JOIN users u ON s.user_id = u.id 
                WHERE n.teacher_id = ? 
                ORDER BY n.created_at DESC 
                LIMIT 10";
$notes_stmt = $conn->prepare($notes_query);
$notes_stmt->bind_param("i", $user_id);
$notes_stmt->execute();
$notes_result = $notes_stmt->get_result();
$recent_notes = [];
while ($row = $notes_result->fetch_assoc()) {
    $recent_notes[] = $row;
}

// Get unread notifications
$unread_count = getUnreadNotificationsCount($user_id, $conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Student Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fa-solid fa-chalkboard-user"></i>

                <span>Teacher</span>
            </div>
            <nav>
                <ul class="sidebar-menu">
                    <li><a href="/student_portal/teacher/dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="/student_portal/teacher/add_note.php"><i class="fas fa-plus-circle"></i> Add Note</a></li>
                    <li><a href="/student_portal/teacher/students.php"><i class="fas fa-users"></i> Students</a></li>
                    <li><a href="/student_portal/teacher/grades.php"><i class="fas fa-chart-bar"></i> Grades</a></li>
                    <li><a href="/student_portal/teacher/chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                    <li><a href="/student_portal/teacher/notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="/student_portal/teacher/settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="/student_portal/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h2>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h2>
                </div>
                <div class="header-right">
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="notification-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="user-profile">
                        <div class="profile-picture"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
                        <span><?php echo htmlspecialchars($full_name); ?></span>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <div class="container">
                    <!-- Statistics -->
                    <div class="grid grid-3">
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">
                                    <?php echo count($students); ?>
                                </div>
                                <div style="color: var(--text-light);">Total Students</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--success-color); margin-bottom: 0.5rem;">
                                    <?php echo count($recent_notes); ?>
                                </div>
                                <div style="color: var(--text-light);">Notes Added</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 0.5rem;">
                                    <?php echo $unread_count; ?>
                                </div>
                                <div style="color: var(--text-light);">Unread Messages</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card" style="margin-top: 2rem;">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                                <a href="/student_portal/teacher/add_note.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Add Note
                                </a>
                                <a href="/student_portal/teacher/students.php" class="btn btn-secondary">
                                    <i class="fas fa-users"></i> View Students
                                </a>
                                <a href="/student_portal/teacher/grades.php" class="btn btn-outline">
                                    <i class="fas fa-chart-bar"></i> Manage Grades
                                </a>
                                <a href="/student_portal/teacher/chat.php" class="btn btn-outline">
                                    <i class="fas fa-comments"></i> Messages
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Notes -->
                    <div class="card" style="margin-top: 2rem;">
                        <div class="card-header">
                            <h3 class="card-title">Recent Notes Added</h3>
                            <a href="/student_portal/teacher/add_note.php" style="color: var(--primary-color);">Add New</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_notes)): ?>
                                <div class="table-responsive">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Type</th>
                                                <th>Subject</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($recent_notes, 0, 5) as $note): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($note['student_name']); ?></td>
                                                    <td><span class="badge badge-<?php echo $note['note_type']; ?>"><?php echo ucfirst($note['note_type']); ?></span></td>
                                                    <td><?php echo htmlspecialchars($note['subject']); ?></td>
                                                    <td><?php echo formatDate($note['created_at']); ?></td>
                                                    <td>
                                                        <a href="/student_portal/teacher/students.php?student_id=<?php echo $note['student_id']; ?>" class="btn btn-sm btn-outline">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p style="text-align: center; color: var(--text-light);">No notes added yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/student_portal/assets/js/main.js"></script>
</body>
</html>

