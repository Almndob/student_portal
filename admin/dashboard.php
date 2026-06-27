<?php
session_start();
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header("Location: /student_portal/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Get statistics
$users_query = "SELECT COUNT(*) as count FROM users";
$users_stmt = $conn->prepare($users_query);
$users_stmt->execute();
$users_result = $users_stmt->get_result();
$users_count = $users_result->fetch_assoc()['count'];

$students_query = "SELECT COUNT(*) as count FROM students";
$students_stmt = $conn->prepare($students_query);
$students_stmt->execute();
$students_result = $students_stmt->get_result();
$students_count = $students_result->fetch_assoc()['count'];

$notes_query = "SELECT COUNT(*) as count FROM notes";
$notes_stmt = $conn->prepare($notes_query);
$notes_stmt->execute();
$notes_result = $notes_stmt->get_result();
$notes_count = $notes_result->fetch_assoc()['count'];

$messages_query = "SELECT COUNT(*) as count FROM messages";
$messages_stmt = $conn->prepare($messages_query);
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result();
$messages_count = $messages_result->fetch_assoc()['count'];

// Get users by role
$role_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$role_stmt = $conn->prepare($role_query);
$role_stmt->execute();
$role_result = $role_stmt->get_result();
$role_stats = [];
while ($row = $role_result->fetch_assoc()) {
    $role_stats[] = $row;
}

// Get recent users
$recent_users_query = "SELECT full_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users_stmt = $conn->prepare($recent_users_query);
$recent_users_stmt->execute();
$recent_users_result = $recent_users_stmt->get_result();
$recent_users = [];
while ($row = $recent_users_result->fetch_assoc()) {
    $recent_users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Student Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fa-solid fa-user-shield"></i>

                <span>Portal</span>
            </div>
            <nav>
                <ul class="sidebar-menu">
                    <li><a href="/student_portal/admin/dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="/student_portal/admin/users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="/student_portal/admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="/student_portal/admin/settings.php"><i class="fas fa-cog"></i> Settings</a></li>
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
                    <div class="user-profile">
                        <div class="profile-picture"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
                        <span><?php echo htmlspecialchars($full_name); ?></span>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <div class="container">
                    <!-- Key Metrics -->
                    <div class="grid grid-4" style="margin-bottom: 2rem;">
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">
                                    <?php echo $users_count; ?>
                                </div>
                                <div style="color: var(--text-light);">Total Users</div>
                                <a href="/student_portal/admin/users.php" style="font-size: 0.85rem; color: var(--primary-color); text-decoration: none; margin-top: 0.5rem; display: inline-block;">
                                    View All →
                                </a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--success-color); margin-bottom: 0.5rem;">
                                    <?php echo $students_count; ?>
                                </div>
                                <div style="color: var(--text-light);">Students</div>
                                <a href="/student_portal/admin/reports.php" style="font-size: 0.85rem; color: var(--success-color); text-decoration: none; margin-top: 0.5rem; display: inline-block;">
                                    View Reports →
                                </a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 0.5rem;">
                                    <?php echo $notes_count; ?>
                                </div>
                                <div style="color: var(--text-light);">Notes Added</div>
                                <p style="font-size: 0.85rem; color: var(--text-light); margin-top: 0.5rem;">Total observations</p>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--warning-color); margin-bottom: 0.5rem;">
                                    <?php echo $messages_count; ?>
                                </div>
                                <div style="color: var(--text-light);">Messages</div>
                                <p style="font-size: 0.85rem; color: var(--text-light); margin-top: 0.5rem;">Total interactions</p>
                            </div>
                        </div>
                    </div>

                    <!-- Charts and Recent Activity -->
                    <div class="grid grid-2" style="margin-bottom: 2rem;">
                        <!-- Users by Role Chart -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Users by Role</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="roleChart" style="max-height: 300px;"></canvas>
                            </div>
                        </div>

                        <!-- Recent Users -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Registrations</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <?php foreach ($recent_users as $user): ?>
                                        <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius); display: flex; justify-content: space-between; align-items: center;">
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <div style="width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                                    <small style="color: var(--text-light);"><?php echo ucfirst($user['role']); ?></small>
                                                </div>
                                            </div>
                                            <small style="color: var(--text-light);"><?php echo formatDate($user['created_at']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                                <a href="/student_portal/admin/users.php" class="btn btn-primary">
                                    <i class="fas fa-users"></i> Manage Users
                                </a>
                                <a href="/student_portal/admin/reports.php" class="btn btn-secondary">
                                    <i class="fas fa-chart-bar"></i> View Reports
                                </a>
                                <a href="/student_portal/register.php" class="btn btn-outline">
                                    <i class="fas fa-user-plus"></i> Add User
                                </a>
                                <a href="/student_portal/admin/settings.php" class="btn btn-outline">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Role Chart
        const roleCtx = document.getElementById('roleChart').getContext('2d');
        new Chart(roleCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(function($r) { return ucfirst($r['role']); }, $role_stats)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(function($r) { return $r['count']; }, $role_stats)); ?>,
                    backgroundColor: ['#3B82F6', '#A78BFA', '#10B981', '#F59E0B'],
                    borderColor: ['#2563EB', '#9333EA', '#059669', '#D97706'],
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

