<?php
session_start();
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SESSION['role'] !== 'counselor') {
    header("Location: /student_portal/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Get report data
$total_sessions = 0;
$completed_sessions = 0;
$scheduled_sessions = 0;
$special_cases = 0;

// Total sessions
$total_query = "SELECT COUNT(*) as total FROM counseling_sessions WHERE counselor_id = ?";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_sessions = $total_stmt->get_result()->fetch_assoc()['total'];

// Completed sessions
$completed_query = "SELECT COUNT(*) as total FROM counseling_sessions WHERE counselor_id = ? AND status = 'completed'";
$completed_stmt = $conn->prepare($completed_query);
$completed_stmt->bind_param("i", $user_id);
$completed_stmt->execute();
$completed_sessions = $completed_stmt->get_result()->fetch_assoc()['total'];

// Scheduled sessions
$scheduled_query = "SELECT COUNT(*) as total FROM counseling_sessions WHERE counselor_id = ? AND status = 'scheduled'";
$scheduled_stmt = $conn->prepare($scheduled_query);
$scheduled_stmt->bind_param("i", $user_id);
$scheduled_stmt->execute();
$scheduled_sessions = $scheduled_stmt->get_result()->fetch_assoc()['total'];

// Special circumstances
$special_query = "SELECT COUNT(DISTINCT student_id) as total FROM special_circumstances WHERE created_by = ?";
$special_stmt = $conn->prepare($special_query);
$special_stmt->bind_param("i", $user_id);
$special_stmt->execute();
$special_cases = $special_stmt->get_result()->fetch_assoc()['total'];

// Get sessions by status
$session_status_query = "SELECT status, COUNT(*) as count FROM counseling_sessions WHERE counselor_id = ? GROUP BY status";
$session_status_stmt = $conn->prepare($session_status_query);
$session_status_stmt->bind_param("i", $user_id);
$session_status_stmt->execute();
$session_status_result = $session_status_stmt->get_result();
$session_statuses = [];
while ($row = $session_status_result->fetch_assoc()) {
    $session_statuses[] = $row;
}

// Get monthly statistics
$monthly_query = "SELECT 
                    DATE_FORMAT(session_date, '%Y-%m') as month,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                  FROM counseling_sessions 
                  WHERE counselor_id = ? 
                  GROUP BY DATE_FORMAT(session_date, '%Y-%m')
                  ORDER BY month DESC
                  LIMIT 6";
$monthly_stmt = $conn->prepare($monthly_query);
$monthly_stmt->bind_param("i", $user_id);
$monthly_stmt->execute();
$monthly_result = $monthly_stmt->get_result();
$monthly_stats = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_stats[] = $row;
}

// Get unread notifications
$unread_count = getUnreadNotificationsCount($user_id, $conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Counselor Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fa-solid fa-user-nurse"></i>
                <span>Counselor</span>
            </div>
            <nav>
                <ul class="sidebar-menu">
                    <li><a href="/student_portal/counselor/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="/student_portal/counselor/students.php"><i class="fas fa-users"></i> Students</a></li>
                    <li><a href="/student_portal/counselor/sessions.php"><i class="fas fa-calendar"></i> Sessions</a></li>
                    <li><a href="/student_portal/counselor/reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="/student_portal/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h2>Reports & Analytics</h2>
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
                    <!-- Summary Statistics -->
                    <div class="grid grid-4" style="margin-bottom: 2rem;">
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">
                                    <?php echo $total_sessions; ?>
                                </div>
                                <div style="color: var(--text-light);">Total Sessions</div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--success-color); margin-bottom: 0.5rem;">
                                    <?php echo $completed_sessions; ?>
                                </div>
                                <div style="color: var(--text-light);">Completed</div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--warning-color); margin-bottom: 0.5rem;">
                                    <?php echo $scheduled_sessions; ?>
                                </div>
                                <div style="color: var(--text-light);">Scheduled</div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--danger-color); margin-bottom: 0.5rem;">
                                    <?php echo $special_cases; ?>
                                </div>
                                <div style="color: var(--text-light);">Special Cases</div>
                            </div>
                        </div>
                    </div>

                    <!-- Session Status -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 class="card-title">Sessions by Status</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($session_statuses)): ?>
                                <div style="display: grid; gap: 1rem;">
                                    <?php foreach ($session_statuses as $status_item): ?>
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--background-color); border-radius: var(--border-radius);">
                                            <span style="font-weight: 600;"><?php echo ucfirst($status_item['status']); ?></span>
                                            <span style="font-size: 1.5rem; color: var(--primary-color);"><?php echo $status_item['count']; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="text-align: center; color: var(--text-light); padding: 2rem;">No session data available</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Monthly Statistics -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Monthly Statistics (Last 6 Months)</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($monthly_stats)): ?>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid var(--border-color);">
                                            <th style="padding: 1rem; text-align: left;">Month</th>
                                            <th style="padding: 1rem; text-align: center;">Total Sessions</th>
                                            <th style="padding: 1rem; text-align: center;">Completed</th>
                                            <th style="padding: 1rem; text-align: center;">Completion Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($monthly_stats as $stat): ?>
                                            <tr style="border-bottom: 1px solid var(--border-color);">
                                                <td style="padding: 1rem;"><?php echo date('F Y', strtotime($stat['month'] . '-01')); ?></td>
                                                <td style="padding: 1rem; text-align: center;"><?php echo $stat['total']; ?></td>
                                                <td style="padding: 1rem; text-align: center;"><?php echo $stat['completed']; ?></td>
                                                <td style="padding: 1rem; text-align: center;">
                                                    <?php 
                                                    $rate = ($stat['total'] > 0) ? round(($stat['completed'] / $stat['total']) * 100) : 0;
                                                    echo $rate . '%';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p style="text-align: center; color: var(--text-light); padding: 2rem;">No monthly statistics available</p>
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
