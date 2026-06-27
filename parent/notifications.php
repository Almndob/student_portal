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

// Get all notifications
$notifications = getNotifications($user_id, $conn, 100);

// Mark as read if requested
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notif_id = (int)$_GET['mark_read'];
    $update_query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ii", $notif_id, $user_id);
    $update_stmt->execute();
    header("Location: /student_portal/parent/notifications.php");
    exit();
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $update_query = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
    header("Location: /student_portal/parent/notifications.php");
    exit();
}

// Filter
$filter = $_GET['filter'] ?? 'all';
$filtered_notifications = $notifications;

if ($filter === 'unread') {
    $filtered_notifications = array_filter($notifications, function($n) {
        return $n['is_read'] == 0;
    });
}

$unread_count = count(array_filter($notifications, function($n) {
    return $n['is_read'] == 0;
}));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Student Portal</title>
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
                    <li><a href="/student_portal/parent/grades.php"><i class="fas fa-chart-bar"></i> Grades</a></li>
                    <li><a href="/student_portal/parent/attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                    <li><a href="/student_portal/parent/notes.php"><i class="fas fa-sticky-note"></i> Notes</a></li>
                    <li><a href="/student_portal/parent/chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                    <li><a href="/student_portal/parent/notifications.php" class="active"><i class="fas fa-bell"></i> Notifications</a></li>
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
                    <h2>Notifications</h2>
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
                    <!-- Filter and Actions -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                <div style="display: flex; gap: 1rem;">
                                    <a href="/student_portal/parent/notifications.php?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">
                                        <i class="fas fa-list"></i> All (<?php echo count($notifications); ?>)
                                    </a>
                                    <a href="/student_portal/parent/notifications.php?filter=unread" class="btn <?php echo $filter === 'unread' ? 'btn-primary' : 'btn-outline'; ?>">
                                        <i class="fas fa-envelope"></i> Unread (<?php echo $unread_count; ?>)
                                    </a>
                                </div>
                                <?php if ($unread_count > 0): ?>
                                    <a href="/student_portal/parent/notifications.php?mark_all_read" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-check-double"></i> Mark All as Read
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications List -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (!empty($filtered_notifications)): ?>
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <?php foreach ($filtered_notifications as $notif): ?>
                                        <div style="padding: 1.5rem; background-color: <?php echo $notif['is_read'] ? 'var(--background-color)' : 'rgba(59, 130, 246, 0.05)'; ?>; border-radius: var(--border-radius); border-left: 4px solid <?php 
                                            echo $notif['type'] === 'message' ? 'var(--primary-color)' :
                                                 ($notif['type'] === 'note' ? 'var(--warning-color)' :
                                                  ($notif['type'] === 'grade' ? 'var(--success-color)' : 'var(--secondary-color)'));
                                        ?>; display: flex; justify-content: space-between; align-items: start;">
                                            <div style="flex: 1;">
                                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                                    <h4 style="margin: 0;">
                                                        <?php echo htmlspecialchars($notif['title']); ?>
                                                        <?php if (!$notif['is_read']): ?>
                                                            <span style="display: inline-block; width: 8px; height: 8px; background-color: var(--primary-color); border-radius: 50%; margin-left: 0.5rem;"></span>
                                                        <?php endif; ?>
                                                    </h4>
                                                </div>
                                                <p style="margin: 0.5rem 0; color: var(--text-color);">
                                                    <?php echo htmlspecialchars($notif['message']); ?>
                                                </p>
                                                <small style="color: var(--text-light);">
                                                    <i class="fas fa-clock"></i> <?php echo formatDateTime($notif['created_at']); ?>
                                                </small>
                                            </div>
                                            <?php if (!$notif['is_read']): ?>
                                                <a href="/student_portal/parent/notifications.php?mark_read=<?php echo $notif['id']; ?>" class="btn btn-sm btn-outline" style="margin-left: 1rem; white-space: nowrap;">
                                                    <i class="fas fa-check"></i> Mark as Read
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div style="text-align: center; padding: 3rem; color: var(--text-light);">
                                    <i class="fas fa-bell-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                    <p>No notifications available</p>
                                </div>
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

