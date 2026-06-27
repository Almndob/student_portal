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

$success = '';
$error = '';

// Handle note submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $note_type = sanitize($_POST['note_type'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $details = sanitize($_POST['details'] ?? '');
    $importance_level = sanitize($_POST['importance_level'] ?? 'medium');

    if ($student_id <= 0 || empty($note_type) || empty($subject) || empty($details)) {
        $error = 'All fields are required';
    } else {
        $insert_query = "INSERT INTO notes (student_id, teacher_id, note_type, subject, details, importance_level) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iissss", $student_id, $user_id, $note_type, $subject, $details, $importance_level);
       // $insert_stmt->bind_param('iissss', $student_id,$teacher_id, $category, $title, $content, $priority);

        if ($insert_stmt->execute()) {
            // Get student's guardian to send notification
            $guardian_query = "SELECT guardian_id FROM students WHERE id = ?";
            $guardian_stmt = $conn->prepare($guardian_query);
            $guardian_stmt->bind_param("i", $student_id);
            $guardian_stmt->execute();
            $guardian_result = $guardian_stmt->get_result();
            $guardian = $guardian_result->fetch_assoc();

            if ($guardian && $guardian['guardian_id']) {
                createNotification($guardian['guardian_id'], 'New Note', "A new $note_type note has been added for your student", 'note', $student_id, $conn);
            }

            $success = 'Note added successfully!';
            // Clear form
            $_POST = [];
        } else {
            $error = 'Error adding note';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Note - Student Portal</title>
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
                    <li><a href="/student_portal/teacher/add_note.php" class="active"><i class="fas fa-plus-circle"></i> Add Note</a></li>
                    <li><a href="/student_portal/teacher/students.php"><i class="fas fa-users"></i> Students</a></li>
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
                    <h2>Add New Note</h2>
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
                        <!-- Form -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Create Note</h3>
                            </div>
                            <div class="card-body">
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

                                <form method="POST">
                                    <div class="form-group">
                                        <label for="student_id">
                                            <i class="fas fa-user"></i> Student
                                        </label>
                                        <select id="student_id" name="student_id" required>
                                            <option value="">-- Select Student --</option>
                                            <?php foreach ($students as $student): ?>
                                                <option value="<?php echo $student['id']; ?>">
                                                    <?php echo htmlspecialchars($student['full_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="note_type">
                                            <i class="fas fa-tag"></i> Note Type
                                        </label>
                                        <select id="note_type" name="note_type" required>
                                            <option value="">-- Select Type --</option>
                                            <option value="academic">Academic</option>
                                            <option value="behavioral">Behavioral</option>
                                            <option value="positive">Positive</option>
                                            <option value="warning">Warning</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="subject">
                                            <i class="fas fa-heading"></i> Subject
                                        </label>
                                        <input type="text" id="subject" name="subject" placeholder="Note subject" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="details">
                                            <i class="fas fa-align-left"></i> Details
                                        </label>
                                        <textarea id="details" name="details" placeholder="Detailed description..." required></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="importance_level">
                                            <i class="fas fa-flag"></i> Importance Level
                                        </label>
                                        <select id="importance_level" name="importance_level">
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                                        <i class="fas fa-paper-plane"></i> Send Note
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Information -->
                        <div>
                            <div class="card" style="margin-bottom: 1.5rem;">
                                <div class="card-header">
                                    <h3 class="card-title">Note Types</h3>
                                </div>
                                <div class="card-body">
                                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                                        <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius); border-left: 4px solid var(--primary-color);">
                                            <strong style="color: var(--primary-color);">Academic</strong>
                                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">Related to academic performance and learning</p>
                                        </div>
                                        <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius); border-left: 4px solid var(--warning-color);">
                                            <strong style="color: var(--warning-color);">Behavioral</strong>
                                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">Related to student behavior and discipline</p>
                                        </div>
                                        <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius); border-left: 4px solid var(--success-color);">
                                            <strong style="color: var(--success-color);">Positive</strong>
                                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">Positive feedback and achievements</p>
                                        </div>
                                        <div style="padding: 1rem; background-color: var(--background-color); border-radius: var(--border-radius); border-left: 4px solid var(--danger-color);">
                                            <strong style="color: var(--danger-color);">Warning</strong>
                                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: var(--text-light);">Important warnings and alerts</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">My Students</h3>
                                </div>
                                <div class="card-body">
                                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                        <?php foreach (array_slice($students, 0, 5) as $student): ?>
                                            <div style="padding: 0.75rem; background-color: var(--background-color); border-radius: var(--border-radius); display: flex; align-items: center; gap: 0.75rem;">
                                                <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 600;">
                                                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                                </div>
                                                <span style="font-size: 0.9rem;"><?php echo htmlspecialchars($student['full_name']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($students) > 5): ?>
                                            <small style="color: var(--text-light); text-align: center;">+<?php echo count($students) - 5; ?> more students</small>
                                        <?php endif; ?>
                                    </div>
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

