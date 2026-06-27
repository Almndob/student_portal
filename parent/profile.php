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
$student_query = "SELECT s.*, u.full_name, u.email FROM students s 
                  JOIN users u ON s.user_id = u.id 
                  WHERE s.guardian_id = ?";
$student_stmt = $conn->prepare($student_query);
$student_stmt->bind_param("i", $user_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student = $student_result->fetch_assoc();

if (!$student) {
    $student = [];
}

$student_id = $student['id'] ?? 0;

// Get grades
$grades = getStudentGrades($student_id, $conn);

// Get attendance
$attendance = getStudentAttendance($student_id, $conn);

// Get notes
$notes = getStudentNotes($student_id, $conn, 20);

// Calculate attendance percentage
$total_attendance = count($attendance);
$present_count = 0;
foreach ($attendance as $att) {
    if ($att['status'] === 'present') $present_count++;
}
$attendance_percentage = $total_attendance > 0 ? round(($present_count / $total_attendance) * 100, 2) : 0;

// Calculate average grade
$average_grade = 0;
if (!empty($grades)) {
    $sum = 0;
    foreach ($grades as $grade) {
        $sum += $grade['grade'];
    }
    $average_grade = round($sum / count($grades), 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - Student Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-tabs {
            display: flex;
            gap: 1rem;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .profile-tab {
            padding: 1rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            color: var(--text-light);
            font-weight: 500;
            transition: var(--transition);
        }

        .profile-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .profile-tab-content {
            display: none;
        }

        .profile-tab-content.active {
            display: block;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>
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
                    <li><a href="/student_portal/parent/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="/student_portal/parent/profile.php" class="active"><i class="fas fa-user"></i> Student Profile</a></li>
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
                    <h2>Student Profile</h2>
                </div>
                <div class="header-right">
                    <div class="user-profile">
                        <div class="profile-picture"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
                        <span><?php echo htmlspecialchars($full_name); ?></span>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <div class="container">
                    <!-- Student Header Card -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-body">
                            <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem;">
                                <div class="profile-picture" style="width: 100px; height: 100px; font-size: 2.5rem;">
                                    <?php echo strtoupper(substr($student['full_name'] ?? 'N', 0, 1)); ?>
                                </div>
                                <div>
                                    <h2><?php echo htmlspecialchars($student['full_name'] ?? 'Not Assigned'); ?></h2>
                                    <p style="margin: 0.5rem 0; color: var(--text-light);">
                                        <i class="fas fa-id-card"></i> ID: <?php echo htmlspecialchars($student['student_id'] ?? 'N/A'); ?>
                                    </p>
                                    <p style="margin: 0.5rem 0; color: var(--text-light);">
                                        <i class="fas fa-book"></i> Class: <?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Statistics -->
                            <div class="grid grid-4">
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo $average_grade; ?></div>
                                    <div class="stat-label">Average Grade</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo $attendance_percentage; ?>%</div>
                                    <div class="stat-label">Attendance</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo count($grades); ?></div>
                                    <div class="stat-label">Total Grades</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo count($notes); ?></div>
                                    <div class="stat-label">Notes</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="profile-tabs">
                        <div class="profile-tab active" onclick="switchTab('academic')">
                            <i class="fas fa-book"></i> Academic Performance
                        </div>
                        <div class="profile-tab" onclick="switchTab('behavior')">
                            <i class="fas fa-heart"></i> Behavior & Discipline
                        </div>
                        <div class="profile-tab" onclick="switchTab('attendance')">
                            <i class="fas fa-calendar-check"></i> Attendance
                        </div>
                        <div class="profile-tab" onclick="switchTab('health')">
                            <i class="fas fa-heartbeat"></i> Health Status
                        </div>
                    </div>

                    <!-- Academic Tab -->
                    <div id="academic" class="profile-tab-content active">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Academic Performance</h3>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($grades)): ?>
                                    <div class="table-responsive">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Grade</th>
                                                    <th>Exam Type</th>
                                                    <th>Date</th>
                                                    <th>Teacher</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($grades as $grade): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($grade['subject']); ?></td>
                                                        <td><strong><?php echo number_format($grade['grade'], 2); ?></strong></td>
                                                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($grade['exam_type']); ?></span></td>
                                                        <td><?php echo formatDate($grade['date_recorded']); ?></td>
                                                        <td><?php echo htmlspecialchars($grade['teacher_id'] ?? 'N/A'); ?></td>
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
                    </div>

                    <!-- Behavior Tab -->
                    <div id="behavior" class="profile-tab-content">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Behavioral Notes</h3>
                            </div>
                            <div class="card-body">
                                <?php 
                                $behavioral_notes = array_filter($notes, function($n) { 
                                    return $n['note_type'] === 'behavioral'; 
                                });
                                if (!empty($behavioral_notes)): 
                                ?>
                                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                                        <?php foreach ($behavioral_notes as $note): ?>
                                            <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius); border-left: 4px solid var(--warning-color);">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                                    <strong><?php echo htmlspecialchars($note['subject']); ?></strong>
                                                    <small style="color: var(--text-light);"><?php echo formatDate($note['created_at']); ?></small>
                                                </div>
                                                <p style="margin: 0.5rem 0; color: var(--text-color);"><?php echo htmlspecialchars($note['details']); ?></p>
                                                <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                                                    <small style="color: var(--text-light);">By: <?php echo htmlspecialchars($note['teacher_name']); ?></small>
                                                    <span class="badge badge-warning"><?php echo ucfirst($note['importance_level']); ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p style="text-align: center; color: var(--text-light);">No behavioral notes available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Tab -->
                    <div id="attendance" class="profile-tab-content">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Attendance Records</h3>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($attendance)): ?>
                                    <div class="table-responsive">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($attendance, 0, 20) as $att): ?>
                                                    <tr>
                                                        <td><?php echo formatDate($att['attendance_date']); ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php echo $att['status'] === 'present' ? 'success' : ($att['status'] === 'absent' ? 'danger' : 'warning'); ?>">
                                                                <?php echo ucfirst($att['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($att['notes'] ?? '-'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p style="text-align: center; color: var(--text-light);">No attendance records available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Health Tab -->
                    <div id="health" class="profile-tab-content">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Health Information</h3>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($student['health_info'])): ?>
                                    <p><?php echo htmlspecialchars($student['health_info']); ?></p>
                                <?php else: ?>
                                    <p style="text-align: center; color: var(--text-light);">No health information available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.profile-tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.profile-tab');
            tabButtons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab
            const selectedTab = document.getElementById(tabName);
            if (selectedTab) {
                selectedTab.classList.add('active');
            }

            // Add active class to clicked button
            event.target.closest('.profile-tab').classList.add('active');
        }
    </script>
    <script src="/student_portal/assets/js/main.js"></script>
</body>
</html>

