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
    $student = [];
}

$student_id = $student['id'] ?? 0;

// Get grades
$grades = getStudentGrades($student_id, $conn);

// Group grades by subject
$grades_by_subject = [];
foreach ($grades as $grade) {
    $subject = $grade['subject'];
    if (!isset($grades_by_subject[$subject])) {
        $grades_by_subject[$subject] = [];
    }
    $grades_by_subject[$subject][] = $grade;
}

// Calculate average per subject
$subject_averages = [];
foreach ($grades_by_subject as $subject => $subject_grades) {
    $sum = 0;
    foreach ($subject_grades as $grade) {
        $sum += $grade['grade'];
    }
    $subject_averages[$subject] = round($sum / count($subject_grades), 2);
}

// Calculate overall average
$overall_average = 0;
if (!empty($grades)) {
    $sum = 0;
    foreach ($grades as $grade) {
        $sum += $grade['grade'];
    }
    $overall_average = round($sum / count($grades), 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades - Student Portal</title>
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
                    <li><a href="/student_portal/parent/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="/student_portal/parent/profile.php"><i class="fas fa-user"></i> Student Profile</a></li>
                    <li><a href="/student_portal/parent/grades.php" class="active"><i class="fas fa-chart-bar"></i> Grades</a></li>
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
                    <h2>Grades & Performance</h2>
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
                    <!-- Summary Cards -->
                    <div class="grid grid-3">
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">
                                    <?php echo $overall_average; ?>
                                </div>
                                <div style="color: var(--text-light);">Overall Average</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--success-color); margin-bottom: 0.5rem;">
                                    <?php echo count($grades); ?>
                                </div>
                                <div style="color: var(--text-light);">Total Grades</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 0.5rem;">
                                    <?php echo count($grades_by_subject); ?>
                                </div>
                                <div style="color: var(--text-light);">Subjects</div>
                            </div>
                        </div>
                    </div>

                    <!-- Subject Averages -->
                    <div class="card" style="margin-top: 2rem;">
                        <div class="card-header">
                            <h3 class="card-title">Average by Subject</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-2">
                                <?php foreach ($subject_averages as $subject => $average): ?>
                                    <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius);">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <strong><?php echo htmlspecialchars($subject); ?></strong>
                                            <span style="font-size: 1.25rem; font-weight: 700; color: var(--primary-color);">
                                                <?php echo $average; ?>
                                            </span>
                                        </div>
                                        <div style="width: 100%; height: 8px; background-color: var(--border-color); border-radius: 4px; overflow: hidden;">
                                            <div style="width: <?php echo min($average * 10, 100); ?>%; height: 100%; background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- All Grades Table -->
                    <div class="card" style="margin-top: 2rem;">
                        <div class="card-header">
                            <h3 class="card-title">All Grades</h3>
                            <button class="btn btn-sm btn-primary" onclick="printContent('grades-table')">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="grades-table">
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
                                                        <td>
                                                            <strong style="font-size: 1.1rem;">
                                                                <?php echo number_format($grade['grade'], 2); ?>
                                                            </strong>
                                                        </td>
                                                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($grade['exam_type']); ?></span></td>
                                                        <td><?php echo formatDate($grade['date_recorded']); ?></td>
                                                        <td><?php echo htmlspecialchars($grade['teacher_id'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                                        <i class="fas fa-inbox"></i> No grades available yet
                                    </p>
                                <?php endif; ?>
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

