<?php session_start(); require_once '../includes/db_config.php'; require_once '../includes/functions.php'; requireLogin(); if ($_SESSION['role'] !== 'teacher') { header("Location: /student_portal/index.php"); exit(); } $user_id = $_SESSION['user_id']; $full_name = $_SESSION['full_name']; $user_info = getUserInfo($user_id, $conn); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Student Portal</title>
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
                    <li><a href="/student_portal/teacher/notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="/student_portal/teacher/settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="/student_portal/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <div class="main-content">
            <header class="header">
                <div class="header-left"><h2>Settings</h2></div>
                <div class="header-right"><div class="user-profile"><div class="profile-picture"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div><span><?php echo htmlspecialchars($full_name); ?></span></div></div>
            </header>
            <div class="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Profile Settings</h3></div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div><label style="color: var(--text-light);">Name</label><p style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($user_info['full_name']); ?></p></div>
                                <div><label style="color: var(--text-light);">Email</label><p style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($user_info['email']); ?></p></div>
                                <button class="btn btn-primary" onclick="location.href='/student_portal/logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
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
