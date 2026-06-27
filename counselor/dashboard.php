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

// Get total counseling sessions
$total_sessions_query = "SELECT COUNT(*) as total FROM counseling_sessions WHERE counselor_id = ?";
$total_sessions_stmt = $conn->prepare($total_sessions_query);
$total_sessions_stmt->bind_param("i", $user_id);
$total_sessions_stmt->execute();
$total_sessions_result = $total_sessions_stmt->get_result();
$total_sessions = $total_sessions_result->fetch_assoc()['total'];

// Get completed sessions count
$completed_sessions_query = "SELECT COUNT(*) as total FROM counseling_sessions WHERE counselor_id = ? AND status = 'completed'";
$completed_sessions_stmt = $conn->prepare($completed_sessions_query);
$completed_sessions_stmt->bind_param("i", $user_id);
$completed_sessions_stmt->execute();
$completed_sessions_result = $completed_sessions_stmt->get_result();
$completed_sessions = $completed_sessions_result->fetch_assoc()['total'];

// Get scheduled sessions count
$scheduled_sessions_query = "SELECT COUNT(*) as total FROM counseling_sessions WHERE counselor_id = ? AND status = 'scheduled'";
$scheduled_sessions_stmt = $conn->prepare($scheduled_sessions_query);
$scheduled_sessions_stmt->bind_param("i", $user_id);
$scheduled_sessions_stmt->execute();
$scheduled_sessions_result = $scheduled_sessions_stmt->get_result();
$scheduled_sessions = $scheduled_sessions_result->fetch_assoc()['total'];

// Get students with special circumstances
$special_circumstances_query = "SELECT COUNT(DISTINCT student_id) as total FROM special_circumstances WHERE created_by = ?";
$special_circumstances_stmt = $conn->prepare($special_circumstances_query);
$special_circumstances_stmt->bind_param("i", $user_id);
$special_circumstances_stmt->execute();
$special_circumstances_result = $special_circumstances_stmt->get_result();
$special_circumstances_count = $special_circumstances_result->fetch_assoc()['total'];

// Get upcoming sessions (next 5)
$upcoming_sessions_query = "SELECT cs.*, s.id as student_id, u.full_name as student_name, s.class_name 
                            FROM counseling_sessions cs
                            JOIN students s ON cs.student_id = s.id
                            JOIN users u ON s.user_id = u.id
                            WHERE cs.counselor_id = ? AND cs.status = 'scheduled'
                            ORDER BY cs.session_date ASC
                            LIMIT 5";
$upcoming_sessions_stmt = $conn->prepare($upcoming_sessions_query);
$upcoming_sessions_stmt->bind_param("i", $user_id);
$upcoming_sessions_stmt->execute();
$upcoming_sessions_result = $upcoming_sessions_stmt->get_result();
$upcoming_sessions = [];
while ($row = $upcoming_sessions_result->fetch_assoc()) {
    $upcoming_sessions[] = $row;
}

// Get recent special circumstances
$recent_circumstances_query = "SELECT sc.*, s.id as student_id, u.full_name as student_name, s.class_name
                               FROM special_circumstances sc
                               JOIN students s ON sc.student_id = s.id
                               JOIN users u ON s.user_id = u.id
                               WHERE sc.created_by = ?
                               ORDER BY sc.created_at DESC
                               LIMIT 5";
$recent_circumstances_stmt = $conn->prepare($recent_circumstances_query);
$recent_circumstances_stmt->bind_param("i", $user_id);
$recent_circumstances_stmt->execute();
$recent_circumstances_result = $recent_circumstances_stmt->get_result();
$recent_circumstances = [];
while ($row = $recent_circumstances_result->fetch_assoc()) {
    $recent_circumstances[] = $row;
}

// Get unread notifications
$unread_count = getUnreadNotificationsCount($user_id, $conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselor Dashboard - Student Portal</title>
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
                    <li><a href="/student_portal/counselor/dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="/student_portal/counselor/students.php"><i class="fas fa-users"></i> Students</a></li>
                    <li><a href="/student_portal/counselor/sessions.php"><i class="fas fa-calendar"></i> Sessions</a></li>
                    <li><a href="/student_portal/counselor/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
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
                    <!-- Statistics Cards -->
                    <div class="grid grid-4">
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
                                    <?php echo $special_circumstances_count; ?>
                                </div>
                                <div style="color: var(--text-light);">Special Cases</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card" style="margin-top: 2rem;">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <a href="/student_portal/counselor/sessions.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Schedule Session
                                </a>
                                <a href="/student_portal/counselor/students.php" class="btn btn-secondary">
                                    <i class="fas fa-users"></i> View Students
                                </a>
                                <a href="/student_portal/counselor/reports.php" class="btn btn-outline">
                                    <i class="fas fa-chart-bar"></i> Generate Report
                                </a>
                               
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Sessions and Special Circumstances -->
                    <div class="grid grid-2" style="margin-top: 2rem;">
                        <!-- Upcoming Sessions -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Upcoming Sessions</h3>
                                <a href="/student_portal/counselor/sessions.php" style="color: var(--primary-color);">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($upcoming_sessions)): ?>
                                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                                        <?php foreach ($upcoming_sessions as $session): ?>
                                            <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius); border-left: 4px solid var(--primary-color);">
                                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                                    <div>
                                                        <h4 style="margin: 0 0 0.25rem 0;"><?php echo htmlspecialchars($session['student_name']); ?></h4>
                                                        <small style="color: var(--text-light);">Class: <?php echo htmlspecialchars($session['class_name']); ?></small>
                                                    </div>
                                                    <span class="badge badge-warning">Scheduled</span>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-light); font-size: 0.9rem;">
                                                    <i class="fas fa-calendar"></i>
                                                    <span><?php echo formatDateTime($session['session_date']); ?></span>
                                                </div>
                                                <?php if ($session['treatment_plan']): ?>
                                                    <p style="margin: 0.5rem 0 0 0; color: var(--text-color); font-size: 0.9rem;">
                                                        <strong>Plan:</strong> <?php echo htmlspecialchars(substr($session['treatment_plan'], 0, 80)); ?>...
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="text-align: center; padding: 2rem;">
                                        <i class="fas fa-calendar-check" style="font-size: 3rem; color: var(--text-light); opacity: 0.3; margin-bottom: 1rem;"></i>
                                        <p style="color: var(--text-light);">No upcoming sessions scheduled</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Special Circumstances -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Special Circumstances</h3>
                                <a href="/student_portal/counselor/students.php" style="color: var(--primary-color);">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recent_circumstances)): ?>
                                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                                        <?php foreach ($recent_circumstances as $circumstance): ?>
                                            <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius); border-left: 4px solid var(--danger-color);">
                                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                                    <div>
                                                        <h4 style="margin: 0 0 0.25rem 0;"><?php echo htmlspecialchars($circumstance['student_name']); ?></h4>
                                                        <small style="color: var(--text-light);">Class: <?php echo htmlspecialchars($circumstance['class_name']); ?></small>
                                                    </div>
                                                    <small style="color: var(--text-light);"><?php echo formatDate($circumstance['created_at']); ?></small>
                                                </div>
                                                <p style="margin: 0; color: var(--text-color); font-size: 0.9rem;">
                                                    <?php echo htmlspecialchars(substr($circumstance['description'], 0, 100)); ?>
                                                    <?php if (strlen($circumstance['description']) > 100) echo '...'; ?>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="text-align: center; padding: 2rem;">
                                        <i class="fas fa-heart" style="font-size: 3rem; color: var(--text-light); opacity: 0.3; margin-bottom: 1rem;"></i>
                                        <p style="color: var(--text-light);">No special circumstances recorded</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Session Statistics Chart Info -->
                    <div class="card" style="margin-top: 2rem;">
                        <div class="card-header">
                            <h3 class="card-title">Session Overview</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; padding: 1rem;">
                                <div style="text-align: center;">
                                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-clipboard-check" style="color: var(--success-color);"></i>
                                    </div>
                                    <h4 style="margin: 0 0 0.5rem 0; color: var(--text-color);">Completion Rate</h4>
                                    <p style="font-size: 2rem; font-weight: 700; color: var(--success-color); margin: 0;">
                                        <?php 
                                        if ($total_sessions > 0) {
                                            echo round(($completed_sessions / $total_sessions) * 100) . '%';
                                        } else {
                                            echo '0%';
                                        }
                                        ?>
                                    </p>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-clock" style="color: var(--warning-color);"></i>
                                    </div>
                                    <h4 style="margin: 0 0 0.5rem 0; color: var(--text-color);">Pending Sessions</h4>
                                    <p style="font-size: 2rem; font-weight: 700; color: var(--warning-color); margin: 0;">
                                        <?php echo $scheduled_sessions; ?>
                                    </p>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-exclamation-triangle" style="color: var(--danger-color);"></i>
                                    </div>
                                    <h4 style="margin: 0 0 0.5rem 0; color: var(--text-color);">Priority Cases</h4>
                                    <p style="font-size: 2rem; font-weight: 700; color: var(--danger-color); margin: 0;">
                                        <?php echo $special_circumstances_count; ?>
                                    </p>
                                </div>
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
