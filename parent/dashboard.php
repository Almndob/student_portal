<?php
session_start();
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SESSION['role'] !== 'parent') {
    header("Location: /student_portal/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Get student information
$student_query = "SELECT s.*, u.full_name FROM students s 
                  JOIN users u ON s.user_id = u.id 
                  WHERE s.guardian_id = ?";
$student_stmt = $conn->prepare($student_query);
$student_stmt->bind_param("i", $user_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student = $student_result->fetch_assoc();

if (!$student) {
    $student = ['full_name' => 'Not Assigned', 'class_name' => 'N/A'];
}

// Get recent notes
$notes = getStudentNotes($student['id'] ?? 0, $conn, 5);

// Get recent grades
$grades = getStudentGrades($student['id'] ?? 0, $conn);

// Get unread notifications
$unread_count = getUnreadNotificationsCount($user_id, $conn);
$notifications = getNotifications($user_id, $conn, 5);

// Get attendance summary
$attendance_query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
                    FROM attendance 
                    WHERE student_id = ?";
$attendance_stmt = $conn->prepare($attendance_query);
$student_id = $student['id'] ?? 0; // اجعلها متغير
$attendance_stmt->bind_param("i", $student_id); // صحيح

//$attendance_stmt->bind_param("ii", $student['id'] ?? 0);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->get_result();
$attendance_summary = $attendance_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - Student Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fa-solid fa-user-check"></i>

                <span>Parent</span>
            </div>
            <nav>
                <ul class="sidebar-menu">
                    <li><a href="/student_portal/parent/dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="/student_portal/parent/profile.php"><i class="fas fa-user"></i> Student Profile</a></li>
                    <li><a href="/student_portal/parent/grades.php"><i class="fas fa-chart-bar"></i> Grades</a></li>
                    <li><a href="/student_portal/parent/attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                    <li><a href="/student_portal/parent/notes.php"><i class="fas fa-sticky-note"></i> Notes</a></li>
                    <li><a href="/student_portal/parent/chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                    <li><a href="/student_portal/parent/notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="/student_portal/parent/reports.php"><i class="fas fa-file-pdf"></i> Reports</a></li>
                    <li><a href="/student_portal/parent/settings.php"><i class="fas fa-cog"></i> Settings</a></li>
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
                    <!-- Student Overview Card -->
                    <div class="grid grid-2">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Student Information</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                    <div class="profile-picture" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                        <?php echo strtoupper(substr($student['full_name'] ?? 'N', 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h4><?php echo htmlspecialchars($student['full_name'] ?? 'Not Assigned'); ?></h4>
                                        <p style="margin: 0; color: var(--text-light);">Class: <?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                                <a href="/student_portal/parent/profile.php" class="btn btn-primary btn-sm">View Full Profile</a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Attendance Summary</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; text-align: center;">
                                    <div>
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">
                                            <?php echo $attendance_summary['present'] ?? 0; ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--text-light);">Present</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger-color);">
                                            <?php echo $attendance_summary['absent'] ?? 0; ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--text-light);">Absent</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning-color);">
                                            <?php echo $attendance_summary['late'] ?? 0; ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--text-light);">Late</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Grades and Notes -->
                    <div class="grid grid-2" style="margin-top: 2rem;">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Grades</h3>
                                <a href="/student_portal/parent/grades.php" style="color: var(--primary-color);">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($grades)): ?>
                                    <div class="table-responsive">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Grade</th>
                                                    <th>Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($grades, 0, 5) as $grade): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($grade['subject']); ?></td>
                                                        <td><strong><?php echo number_format($grade['grade'], 2); ?></strong></td>
                                                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($grade['exam_type']); ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p style="text-align: center; color: var(--text-light);">No grades available yet</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Notes</h3>
                                <a href="/student_portal/parent/notes.php" style="color: var(--primary-color);">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($notes)): ?>
                                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                                        <?php foreach (array_slice($notes, 0, 3) as $note): ?>
                                            <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius); border-left: 4px solid var(--primary-color);">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                                    <span class="badge badge-<?php echo $note['note_type']; ?>">
                                                        <?php echo ucfirst($note['note_type']); ?>
                                                    </span>
                                                    <small style="color: var(--text-light);"><?php echo formatDate($note['created_at']); ?></small>
                                                </div>
                                                <p style="margin: 0; color: var(--text-color);"><?php echo htmlspecialchars(substr($note['details'], 0, 100)); ?>...</p>
                                                <small style="color: var(--text-light);">By: <?php echo htmlspecialchars($note['teacher_name']); ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p style="text-align: center; color: var(--text-light);">No notes yet</p>
                                <?php endif; ?>
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
                                <a href="/student_portal/parent/chat.php" class="btn btn-primary">
                                    <i class="fas fa-comments"></i> Send Message
                                </a>
                                <a href="/student_portal/parent/reports.php" class="btn btn-secondary">
                                    <i class="fas fa-file-pdf"></i> Download Report
                                </a>
                                <a href="/student_portal/parent/notifications.php" class="btn btn-outline">
                                    <i class="fas fa-bell"></i> View Notifications
                                </a>
                                <a href="/student_portal/parent/settings.php" class="btn btn-outline">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/student_portal/assets/js/main.js"></script>
</body>
</html>

