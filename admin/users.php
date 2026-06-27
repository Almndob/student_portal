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

// Get all users
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_stmt = $conn->prepare($users_query);
$users_stmt->execute();
$users_result = $users_stmt->get_result();
$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[] = $row;
}

// Count users by role
$role_counts = [];
foreach ($users as $user) {
    if (!isset($role_counts[$user['role']])) {
        $role_counts[$user['role']] = 0;
    }
    $role_counts[$user['role']]++;
}

$success = '';
$error = '';

// Handle user deletion
if (isset($_GET['delete_user']) && is_numeric($_GET['delete_user'])) {
    $delete_user_id = (int)$_GET['delete_user'];
    if ($delete_user_id != $user_id) {
        $delete_query = "DELETE FROM users WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $delete_user_id);
        if ($delete_stmt->execute()) {
            $success = 'User deleted successfully';
            header("Location: /student_portal/admin/users.php");
            exit();
        } else {
            $error = 'Error deleting user';
        }
    } else {
        $error = 'Cannot delete your own account';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Student Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <li><a href="/student_portal/admin/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="/student_portal/admin/users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
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
                    <h2>Users Management</h2>
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

                    <!-- Statistics -->
                    <div class="grid grid-4" style="margin-bottom: 2rem;">
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">
                                    <?php echo count($users); ?>
                                </div>
                                <div style="color: var(--text-light);">Total Users</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--success-color); margin-bottom: 0.5rem;">
                                    <?php echo $role_counts['parent'] ?? 0; ?>
                                </div>
                                <div style="color: var(--text-light);">Parents</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 0.5rem;">
                                    <?php echo $role_counts['teacher'] ?? 0; ?>
                                </div>
                                <div style="color: var(--text-light);">Teachers</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--warning-color); margin-bottom: 0.5rem;">
                                    <?php echo ($role_counts['counselor'] ?? 0) + ($role_counts['admin'] ?? 0); ?>
                                </div>
                                <div style="color: var(--text-light);">Staff</div>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">All Users</h3>
                            <a href="/student_portal/register.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus-circle"></i> Add User
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Phone</th>
                                            <th>Joined</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $u): ?>
                                            <tr>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                        <div style="width: 35px; height: 35px; border-radius: 50%; background-color: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.85rem;">
                                                            <?php echo strtoupper(substr($u['full_name'], 0, 1)); ?>
                                                        </div>
                                                        <span><?php echo htmlspecialchars($u['full_name']); ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                                                <td><?php echo htmlspecialchars($u['phone'] ?? '-'); ?></td>
                                                <td><?php echo formatDate($u['created_at']); ?></td>
                                                <td>
                                                    <div style="display: flex; gap: 0.5rem;">
                                                        <button class="btn btn-sm btn-outline" onclick="alert('Edit feature coming soon')">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if ($u['id'] != $user_id): ?>
                                                            <a href="/student_portal/admin/users.php?delete_user=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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

