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

// Get data for report
$grades = getStudentGrades($student_id, $conn);
$attendance = getStudentAttendance($student_id, $conn);
$notes = getStudentNotes($student_id, $conn, 100);

// Calculate statistics
$average_grade = 0;
if (!empty($grades)) {
    $sum = 0;
    foreach ($grades as $grade) {
        $sum += $grade['grade'];
    }
    $average_grade = round($sum / count($grades), 2);
}

$total_attendance = count($attendance);
$present_count = 0;
foreach ($attendance as $att) {
    if ($att['status'] === 'present') $present_count++;
}
$attendance_percentage = $total_attendance > 0 ? round(($present_count / $total_attendance) * 100, 2) : 0;

// Count notes by type
$academic_count = count(array_filter($notes, function($n) { return $n['note_type'] === 'academic'; }));
$behavioral_count = count(array_filter($notes, function($n) { return $n['note_type'] === 'behavioral'; }));
$positive_count = count(array_filter($notes, function($n) { return $n['note_type'] === 'positive'; }));
$warning_count = count(array_filter($notes, function($n) { return $n['note_type'] === 'warning'; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Student Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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
                    <li><a href="/student_portal/parent/grades.php"><i class="fas fa-chart-bar"></i> Grades</a></li>
                    <li><a href="/student_portal/parent/attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                    <li><a href="/student_portal/parent/notes.php"><i class="fas fa-sticky-note"></i> Notes</a></li>
                    <li><a href="/student_portal/parent/chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                    <li><a href="/student_portal/parent/notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="/student_portal/parent/reports.php" class="active"><i class="fas fa-file-pdf"></i> Reports</a></li>
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
                    <h2>Performance Reports</h2>
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
                    <!-- Report Actions -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-body">
                            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                <button class="btn btn-primary" onclick="printContent('report-content')">
                                    <i class="fas fa-print"></i> Print Report
                                </button>
                                <button class="btn btn-secondary" onclick="exportToCSV('student_report.csv', {headers: ['Metric', 'Value'], rows: [['Average Grade', '<?php echo $average_grade; ?>'], ['Attendance %', '<?php echo $attendance_percentage; ?>%'], ['Total Grades', '<?php echo count($grades); ?>'], ['Total Notes', '<?php echo count($notes); ?>']]})">
                                    <i class="fas fa-download"></i> Export CSV
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Report Content -->
                    <div id="report-content">
                        <!-- Header -->
                        <div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white;">
                            <div class="card-body">
                                <h2 style="margin: 0; color: white;">Student Performance Report</h2>
                                <p style="margin: 0.5rem 0; color: rgba(255,255,255,0.9);">
                                    Generated on <?php echo date('F d, Y'); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Student Info -->
                        <div class="grid grid-2" style="margin-bottom: 2rem;">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Student Information</h3>
                                </div>
                                <div class="card-body">
                                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                                        <div>
                                            <label style="color: var(--text-light); font-size: 0.9rem;">Student Name</label>
                                            <p style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div>
                                            <label style="color: var(--text-light); font-size: 0.9rem;">Student ID</label>
                                            <p style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($student['student_id'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div>
                                            <label style="color: var(--text-light); font-size: 0.9rem;">Class</label>
                                            <p style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Key Metrics</h3>
                                </div>
                                <div class="card-body">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
                                        <div>
                                            <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary-color);">
                                                <?php echo $average_grade; ?>
                                            </div>
                                            <small style="color: var(--text-light);">Average Grade</small>
                                        </div>
                                        <div>
                                            <div style="font-size: 1.75rem; font-weight: 700; color: var(--success-color);">
                                                <?php echo $attendance_percentage; ?>%
                                            </div>
                                            <small style="color: var(--text-light);">Attendance</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Performance -->
                        <div class="card" style="margin-bottom: 2rem;">
                            <div class="card-header">
                                <h3 class="card-title">Academic Performance</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center;">
                                    <div>
                                        <canvas id="gradesChart" style="max-height: 250px;"></canvas>
                                    </div>
                                    <div>
                                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                                            <div>
                                                <label style="color: var(--text-light); font-size: 0.9rem;">Total Grades Recorded</label>
                                                <p style="margin: 0; font-size: 1.5rem; font-weight: 700;"><?php echo count($grades); ?></p>
                                            </div>
                                            <div>
                                                <label style="color: var(--text-light); font-size: 0.9rem;">Average Grade</label>
                                                <p style="margin: 0; font-size: 1.5rem; font-weight: 700;"><?php echo $average_grade; ?></p>
                                            </div>
                                            <div>
                                                <label style="color: var(--text-light); font-size: 0.9rem;">Performance Status</label>
                                                <p style="margin: 0; font-size: 1.5rem; font-weight: 700;">
                                                    <?php 
                                                    if ($average_grade >= 90) echo '<span style="color: var(--success-color);">Excellent</span>';
                                                    elseif ($average_grade >= 80) echo '<span style="color: var(--primary-color);">Very Good</span>';
                                                    elseif ($average_grade >= 70) echo '<span style="color: var(--warning-color);">Good</span>';
                                                    else echo '<span style="color: var(--danger-color);">Needs Improvement</span>';
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Report -->
                        <div class="card" style="margin-bottom: 2rem;">
                            <div class="card-header">
                                <h3 class="card-title">Attendance Report</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center;">
                                    <div>
                                        <canvas id="attendanceChart" style="max-height: 250px;"></canvas>
                                    </div>
                                    <div>
                                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                                            <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius);">
                                                <label style="color: var(--text-light); font-size: 0.9rem;">Present Days</label>
                                                <p style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--success-color);"><?php echo $present_count; ?> days</p>
                                            </div>
                                            <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius);">
                                                <label style="color: var(--text-light); font-size: 0.9rem;">Total Days</label>
                                                <p style="margin: 0; font-size: 1.25rem; font-weight: 700;"><?php echo $total_attendance; ?> days</p>
                                            </div>
                                            <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius);">
                                                <label style="color: var(--text-light); font-size: 0.9rem;">Attendance Rate</label>
                                                <p style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--primary-color);"><?php echo $attendance_percentage; ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Summary -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Notes & Observations Summary</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                                    <div style="padding: 1rem; background-color: rgba(59, 130, 246, 0.1); border-radius: var(--border-radius); text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);"><?php echo $academic_count; ?></div>
                                        <small style="color: var(--text-light);">Academic Notes</small>
                                    </div>
                                    <div style="padding: 1rem; background-color: rgba(245, 158, 11, 0.1); border-radius: var(--border-radius); text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning-color);"><?php echo $behavioral_count; ?></div>
                                        <small style="color: var(--text-light);">Behavioral Notes</small>
                                    </div>
                                    <div style="padding: 1rem; background-color: rgba(16, 185, 129, 0.1); border-radius: var(--border-radius); text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);"><?php echo $positive_count; ?></div>
                                        <small style="color: var(--text-light);">Positive Notes</small>
                                    </div>
                                    <div style="padding: 1rem; background-color: rgba(239, 68, 68, 0.1); border-radius: var(--border-radius); text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger-color);"><?php echo $warning_count; ?></div>
                                        <small style="color: var(--text-light);">Warnings</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Grades Chart
        const gradesCtx = document.getElementById('gradesChart').getContext('2d');
        new Chart(gradesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($g) { return $g['subject']; }, array_slice($grades, 0, 10))); ?>,
                datasets: [{
                    label: 'Grades',
                    data: <?php echo json_encode(array_map(function($g) { return $g['grade']; }, array_slice($grades, 0, 10))); ?>,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            font: {
                                family: "'Poppins', sans-serif"
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Attendance Chart
        const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(attendanceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    data: [<?php echo $present_count; ?>, <?php echo $total_attendance - $present_count; ?>, 0],
                    backgroundColor: ['#10B981', '#EF4444', '#F59E0B'],
                    borderColor: ['#059669', '#DC2626', '#D97706'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: "'Poppins', sans-serif"
                            }
                        }
                    }
                }
            }
        });
    </script>
    <script src="/student_portal/assets/js/main.js"></script>
</body>
</html>

