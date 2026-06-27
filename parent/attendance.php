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

// Get attendance
$attendance = getStudentAttendance($student_id, $conn);

// Calculate statistics
$total = count($attendance);
$present = 0;
$absent = 0;
$late = 0;

foreach ($attendance as $att) {
    if ($att['status'] === 'present') $present++;
    elseif ($att['status'] === 'absent') $absent++;
    elseif ($att['status'] === 'late') $late++;
}

$present_percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
$absent_percentage = $total > 0 ? round(($absent / $total) * 100, 2) : 0;
$late_percentage = $total > 0 ? round(($late / $total) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Student Portal</title>
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
                    <li><a href="/student_portal/parent/attendance.php" class="active"><i class="fas fa-calendar-check"></i> Attendance</a></li>
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
                    <h2>Attendance Records</h2>
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
                    <!-- Statistics Cards -->
                    <div class="grid grid-4">
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--success-color); margin-bottom: 0.5rem;">
                                    <?php echo $present; ?>
                                </div>
                                <div style="color: var(--text-light);">Present</div>
                                <div style="font-size: 0.85rem; color: var(--text-light);"><?php echo $present_percentage; ?>%</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--danger-color); margin-bottom: 0.5rem;">
                                    <?php echo $absent; ?>
                                </div>
                                <div style="color: var(--text-light);">Absent</div>
                                <div style="font-size: 0.85rem; color: var(--text-light);"><?php echo $absent_percentage; ?>%</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--warning-color); margin-bottom: 0.5rem;">
                                    <?php echo $late; ?>
                                </div>
                                <div style="color: var(--text-light);">Late</div>
                                <div style="font-size: 0.85rem; color: var(--text-light);"><?php echo $late_percentage; ?>%</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">
                                    <?php echo $total; ?>
                                </div>
                                <div style="color: var(--text-light);">Total Days</div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart -->
                    <div class="card" style="margin-top: 2rem;">
                        <div class="card-header">
                            <h3 class="card-title">Attendance Overview</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="attendanceChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>

                    <!-- Attendance Table -->
                    <div class="card" style="margin-top: 2rem;">
                        <div class="card-header">
                            <h3 class="card-title">Attendance Details</h3>
                            <button class="btn btn-sm btn-primary" onclick="printContent('attendance-table')">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="attendance-table">
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
                                                <?php foreach ($attendance as $att): ?>
                                                    <tr>
                                                        <td><?php echo formatDate($att['attendance_date']); ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php 
                                                                echo $att['status'] === 'present' ? 'success' : 
                                                                     ($att['status'] === 'absent' ? 'danger' : 'warning'); 
                                                            ?>">
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
                                    <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                                        <i class="fas fa-inbox"></i> No attendance records available
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Create attendance chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    data: [<?php echo $present; ?>, <?php echo $absent; ?>, <?php echo $late; ?>],
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
                                family: "'Poppins', sans-serif",
                                size: 12
                            },
                            padding: 20
                        }
                    }
                }
            }
        });
    </script>
    <script src="/student_portal/assets/js/main.js"></script>
</body>
</html>

