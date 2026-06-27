<?php
session_start();
require_once '../includes/db_config.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SESSION['role'] !== 'teacher') {
    header("Location: /student_portal/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Get teacher's students
$students = getTeacherStudents($user_id, $conn);

// Get selected student
$selected_student_id = $_GET['student_id'] ?? 0;
$selected_student = null;
$student_notes = [];
$student_grades = [];

if ($selected_student_id > 0) {
    foreach ($students as $student) {
        if ($student['id'] == $selected_student_id) {
            $selected_student = $student;
            break;
        }
    }

    if ($selected_student) {
        $student_notes = getStudentNotes($selected_student_id, $conn, 10);
        $student_grades = getStudentGrades($selected_student_id, $conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Student Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fa-solid fa-chalkboard-user"></i>

                <span>Teacher</span>

            </div>
            <nav>
                <ul class="sidebar-menu">
                    <li><a href="/student_portal/teacher/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="/student_portal/teacher/add_note.php"><i class="fas fa-plus-circle"></i> Add Note</a></li>
                    <li><a href="/student_portal/teacher/students.php" class="active"><i class="fas fa-users"></i> Students</a></li>
                    <li><a href="/student_portal/teacher/grades.php"><i class="fas fa-chart-bar"></i> Grades</a></li>
                    <li><a href="/student_portal/teacher/chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                    <li><a href="/student_portal/teacher/notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="/student_portal/teacher/settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="/student_portal/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h2>My Students</h2>
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
                    <div class="grid grid-2">
                        <!-- Students List -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Students List</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 600px; overflow-y: auto;">
                                    <?php foreach ($students as $student): ?>
                                        <div class="<?php echo $student['id'] == $selected_student_id ? 'card' : ''; ?>" 
                                             style="padding: 1rem; background-color: <?php echo $student['id'] == $selected_student_id ? 'var(--primary-color)' : 'var(--background-color)'; ?>; border-radius: var(--border-radius); cursor: pointer; transition: var(--transition);"
                                             onclick="location.href='/student_portal/teacher/students.php?student_id=<?php echo $student['id']; ?>'">
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <div style="width: 40px; height: 40px; border-radius: 50%; background-color: <?php echo $student['id'] == $selected_student_id ? 'rgba(255,255,255,0.3)' : 'var(--primary-color)'; ?>; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; flex-shrink: 0;">
                                                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                                </div>
                                                <div style="flex: 1; min-width: 0;">
                                                    <div style="font-weight: 600; color: <?php echo $student['id'] == $selected_student_id ? 'white' : 'var(--text-color)'; ?>; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                        <?php echo htmlspecialchars($student['full_name']); ?>
                                                    </div>
                                                    <small style="color: <?php echo $student['id'] == $selected_student_id ? 'rgba(255,255,255,0.7)' : 'var(--text-light)'; ?>;">
                                                        <?php echo htmlspecialchars($student['class_name']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Student Details -->
                        <div>
                            <?php if ($selected_student): ?>
                                <div class="card" style="margin-bottom: 1.5rem;">
                                    <div class="card-header">
                                        <h3 class="card-title">Student Details</h3>
                                    </div>
                                    <div class="card-body">
                                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                                            <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 600;">
                                                <?php echo strtoupper(substr($selected_student['full_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h4 style="margin: 0;"><?php echo htmlspecialchars($selected_student['full_name']); ?></h4>
                                                <p style="margin: 0.25rem 0; color: var(--text-light);">Class: <?php echo htmlspecialchars($selected_student['class_name']); ?></p>
                                            </div>
                                        </div>

                                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                                            <a href="/student_portal/teacher/add_note.php?student_id=<?php echo $selected_student['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-plus-circle"></i> Add Note
                                            </a>
                                            <a href="/student_portal/teacher/grades.php?student_id=<?php echo $selected_student['id']; ?>" class="btn btn-secondary">
                                                <i class="fas fa-chart-bar"></i> View Grades
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recent Notes -->
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Recent Notes</h3>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($student_notes)): ?>
                                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                                <?php foreach (array_slice($student_notes, 0, 3) as $note): ?>
                                                    <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius); border-left: 4px solid var(--primary-color);">
                                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                                            <span class="badge badge-<?php echo $note['note_type']; ?>">
                                                                <?php echo ucfirst($note['note_type']); ?>
                                                            </span>
                                                            <small style="color: var(--text-light);"><?php echo formatDate($note['created_at']); ?></small>
                                                        </div>
                                                        <p style="margin: 0; font-size: 0.9rem; color: var(--text-color);">
                                                            <?php echo htmlspecialchars(substr($note['details'], 0, 80)); ?>...
                                                        </p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p style="text-align: center; color: var(--text-light);">No notes yet</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card">
                                    <div class="card-body" style="text-align: center; padding: 3rem;">
                                        <i class="fas fa-user-check" style="font-size: 3rem; color: var(--text-light); opacity: 0.5; margin-bottom: 1rem;"></i>
                                        <p style="color: var(--text-light);">Select a student to view details</p>
                                    </div>
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

