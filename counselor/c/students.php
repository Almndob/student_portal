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

// Get all students
$students_query = "SELECT s.*, u.full_name, u.email, u.phone,
                   gu.full_name as guardian_name, gu.phone as guardian_phone
                   FROM students s
                   JOIN users u ON s.user_id = u.id
                   LEFT JOIN users gu ON s.guardian_id = gu.id
                   ORDER BY u.full_name ASC";
$students_result = $conn->query($students_query);
$students = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
}

// Get unread notifications
$unread_count = getUnreadNotificationsCount($user_id, $conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Counselor Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .search-bar {
            margin-bottom: 1.5rem;
        }
        .search-bar input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
        }
        .student-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }
        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .student-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        .student-info h3 {
            margin: 0 0 0.5rem 0;
            color: var(--text-color);
        }
        .student-info p {
            margin: 0.25rem 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .student-actions {
            display: flex;
            gap: 0.5rem;
        }
        .student-actions .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
    </style>
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
                    <li><a href="/student_portal/counselor/students.php" class="active"><i class="fas fa-users"></i> Students</a></li>
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
                    <h2>Students Management</h2>
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
                    <!-- Search Bar -->
                    <div class="search-bar">
                        <input type="text" id="searchInput" placeholder="Search students by name, email, or class..." onkeyup="searchStudents()">
                    </div>

                    <!-- Students List -->
                    <div id="studentsList">
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $student): ?>
                                <div class="student-card" data-student-name="<?php echo strtolower($student['full_name']); ?>" data-student-email="<?php echo strtolower($student['email']); ?>" data-student-class="<?php echo strtolower($student['class_name']); ?>">
                                    <div class="student-header">
                                        <div class="student-info">
                                            <h3><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($student['email']); ?></p>
                                            <p><i class="fas fa-graduation-cap"></i> Class: <?php echo htmlspecialchars($student['class_name']); ?></p>
                                            <?php if ($student['guardian_name']): ?>
                                                <p><i class="fas fa-user"></i> Guardian: <?php echo htmlspecialchars($student['guardian_name']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($student['guardian_phone']): ?>
                                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($student['guardian_phone']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="student-actions">
                                            <a href="/student_portal/counselor/sessions.php?student_id=<?php echo $student['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-calendar-plus"></i> Schedule Session
                                            </a>
                                          
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="card">
                                <div class="card-body" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-users" style="font-size: 4rem; color: var(--text-light); opacity: 0.3; margin-bottom: 1rem;"></i>
                                    <p style="color: var(--text-light); font-size: 1.1rem;">No students found</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function searchStudents() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const studentCards = document.querySelectorAll('.student-card');
            
            studentCards.forEach(card => {
                const name = card.getAttribute('data-student-name');
                const email = card.getAttribute('data-student-email');
                const className = card.getAttribute('data-student-class');
                
                if (name.includes(searchTerm) || email.includes(searchTerm) || className.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
    <script src="/student_portal/assets/js/main.js"></script>
</body>
</html>
