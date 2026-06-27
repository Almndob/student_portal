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

// Get user info
$user_info = getUserInfo($user_id, $conn);

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $new_full_name = sanitize($_POST['full_name'] ?? '');
        $new_email = sanitize($_POST['email'] ?? '');
        $new_phone = sanitize($_POST['phone'] ?? '');

        if (empty($new_full_name) || empty($new_email)) {
            $error = 'Name and email are required';
        } else {
            $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sssi", $new_full_name, $new_email, $new_phone, $user_id);

            if ($update_stmt->execute()) {
                $_SESSION['full_name'] = $new_full_name;
                $user_info['full_name'] = $new_full_name;
                $user_info['email'] = $new_email;
                $user_info['phone'] = $new_phone;
                $success = 'Profile updated successfully';
            } else {
                $error = 'Error updating profile';
            }
        }
    } elseif ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All password fields are required';
        } elseif (!verifyPassword($current_password, $user_info['password'])) {
            $error = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters';
        } else {
            $hashed_password = hashPassword($new_password);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $hashed_password, $user_id);

            if ($update_stmt->execute()) {
                $success = 'Password changed successfully';
            } else {
                $error = 'Error changing password';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Student Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-tabs {
            display: flex;
            gap: 1rem;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .settings-tab {
            padding: 1rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            color: var(--text-light);
            font-weight: 500;
            transition: var(--transition);
        }

        .settings-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .settings-tab-content {
            display: none;
        }

        .settings-tab-content.active {
            display: block;
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
                    <li><a href="/student_portal/parent/profile.php"><i class="fas fa-user"></i> Student Profile</a></li>
                    <li><a href="/student_portal/parent/grades.php"><i class="fas fa-chart-bar"></i> Grades</a></li>
                    <li><a href="/student_portal/parent/attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                    <li><a href="/student_portal/parent/notes.php"><i class="fas fa-sticky-note"></i> Notes</a></li>
                    <li><a href="/student_portal/parent/chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                    <li><a href="/student_portal/parent/notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="/student_portal/parent/reports.php"><i class="fas fa-file-pdf"></i> Reports</a></li>
                    <li><a href="/student_portal/parent/settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="/student_portal/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h2>Settings</h2>
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
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo $success; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo $error; ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Tabs -->
                    <div class="settings-tabs">
                        <div class="settings-tab active" onclick="switchTab('profile')">
                            <i class="fas fa-user"></i> Profile
                        </div>
                        <div class="settings-tab" onclick="switchTab('password')">
                            <i class="fas fa-lock"></i> Password
                        </div>
                        <div class="settings-tab" onclick="switchTab('preferences')">
                            <i class="fas fa-sliders-h"></i> Preferences
                        </div>
                    </div>

                    <!-- Profile Tab -->
                    <div id="profile" class="settings-tab-content active">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Profile Information</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_profile">

                                    <div class="form-group">
                                        <label for="full_name">Full Name</label>
                                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_info['full_name']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="username">Username (Read-only)</label>
                                        <input type="text" id="username" value="<?php echo htmlspecialchars($user_info['username']); ?>" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="role">Role (Read-only)</label>
                                        <input type="text" id="role" value="<?php echo ucfirst($user_info['role']); ?>" disabled>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Password Tab -->
                    <div id="password" class="settings-tab-content">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Change Password</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="change_password">

                                    <div class="form-group">
                                        <label for="current_password">Current Password</label>
                                        <input type="password" id="current_password" name="current_password" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input type="password" id="new_password" name="new_password" required>
                                        <small style="color: var(--text-light);">Minimum 6 characters</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="confirm_password">Confirm Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key"></i> Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Preferences Tab -->
                    <div id="preferences" class="settings-tab-content">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Notification Preferences</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <label style="display: flex; align-items: center; gap: 1rem; cursor: pointer;">
                                        <input type="checkbox" checked>
                                        <span>Email notifications for new notes</span>
                                    </label>

                                    <label style="display: flex; align-items: center; gap: 1rem; cursor: pointer;">
                                        <input type="checkbox" checked>
                                        <span>Email notifications for new messages</span>
                                    </label>

                                    <label style="display: flex; align-items: center; gap: 1rem; cursor: pointer;">
                                        <input type="checkbox" checked>
                                        <span>Email notifications for grade updates</span>
                                    </label>

                                    <label style="display: flex; align-items: center; gap: 1rem; cursor: pointer;">
                                        <input type="checkbox" checked>
                                        <span>Email notifications for attendance alerts</span>
                                    </label>

                                    <button class="btn btn-primary" style="width: fit-content;">
                                        <i class="fas fa-save"></i> Save Preferences
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card" style="margin-top: 2rem;">
                            <div class="card-header">
                                <h3 class="card-title">Account</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <p style="color: var(--text-light);">
                                        Account created: <?php echo formatDate($user_info['created_at']); ?>
                                    </p>
                                    <p style="color: var(--text-light);">
                                        Last updated: <?php echo formatDate($user_info['updated_at']); ?>
                                    </p>
                                    <button class="btn btn-danger" onclick="if(confirm('Are you sure? This action cannot be undone.')) { location.href='/student_portal/logout.php'; }">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </div>
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
            const tabs = document.querySelectorAll('.settings-tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.settings-tab');
            tabButtons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab
            const selectedTab = document.getElementById(tabName);
            if (selectedTab) {
                selectedTab.classList.add('active');
            }

            // Add active class to clicked button
            event.target.closest('.settings-tab').classList.add('active');
        }
    </script>
    <script src="/student_portal/assets/js/main.js"></script>
</body>
</html>

