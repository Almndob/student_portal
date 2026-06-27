<?php session_start(); require_once '../includes/db_config.php'; require_once '../includes/functions.php'; requireLogin(); if ($_SESSION['role'] !== 'teacher') { header("Location: /student_portal/index.php"); exit(); } $user_id = $_SESSION['user_id']; $full_name = $_SESSION['full_name']; $notifications = getNotifications($user_id, $conn, 100); ?>
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
        <aside class="sidebar">
            <div class="sidebar-logo"><i class="fa-solid fa-chalkboard-user"></i>
                <span>Teacher</span>
            </div>
            <nav>
                <ul class="sidebar-menu">
                    <li><a href="/student_portal/teacher/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="/student_portal/teacher/add_note.php"><i class="fas fa-plus-circle"></i> Add Note</a></li>
                    <li><a href="/student_portal/teacher/students.php"><i class="fas fa-users"></i> Students</a></li>
                    <li><a href="/student_portal/teacher/grades.php"><i class="fas fa-chart-bar"></i> Grades</a></li>
                    <li><a href="/student_portal/teacher/chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                    <li><a href="/student_portal/teacher/notifications.php" class="active"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="/student_portal/teacher/settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="/student_portal/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <div class="main-content">
            <header class="header">
                <div class="header-left"><h2>Notifications</h2></div>
                <div class="header-right"><div class="user-profile"><div class="profile-picture"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div><span><?php echo htmlspecialchars($full_name); ?></span></div></div>
            </header>
            <div class="content">
                <div class="container">
                    <div class="card">
                        <div class="card-body">
                            <?php if (!empty($notifications)): ?>
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <?php foreach ($notifications as $notif): ?>
                                        <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius); border-left: 4px solid var(--primary-color);">
                                            <h4 style="margin: 0;"><?php echo htmlspecialchars($notif['title']); ?></h4>
                                            <p style="margin: 0.5rem 0; color: var(--text-light);"><?php echo htmlspecialchars($notif['message']); ?></p>
                                            <small style="color: var(--text-light);"><?php echo formatDateTime($notif['created_at']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="text-align: center; color: var(--text-light);">No notifications</p>
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
