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

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $subject = sanitize($_POST['subject'] ?? '');
    $grade = (float)($_POST['grade'] ?? 0);
    $exam_type = sanitize($_POST['exam_type'] ?? '');

    if ($student_id <= 0 || empty($subject) || $grade <= 0 || empty($exam_type)) {
        $error = 'All fields are required and grade must be greater than 0';
    } else {
        $insert_query = "INSERT INTO grades (student_id, subject, grade, exam_type, teacher_id, date_recorded) 
                        VALUES (?, ?, ?, ?, ?, CURDATE())";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("isssi", $student_id, $subject, $grade, $exam_type, $user_id);

        if ($insert_stmt->execute()) {
            // Create notification for guardian
            $guardian_query = "SELECT guardian_id FROM students WHERE id = ?";
            $guardian_stmt = $conn->prepare($guardian_query);
            $guardian_stmt->bind_param("i", $student_id);
            $guardian_stmt->execute();
            $guardian_result = $guardian_stmt->get_result();
            $guardian = $guardian_result->fetch_assoc();

            if ($guardian && $guardian['guardian_id']) {
                createNotification($guardian['guardian_id'], 'New Grade', "A new grade has been recorded for your student in $subject", 'grade', $student_id, $conn);
            }

            $success = 'Grade added successfully!';
            $_POST = [];
        } else {
            $error = 'Error adding grade';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades - Student Portal</title>
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
                    <li><a href="/student_portal/teacher/students.php"><i class="fas fa-users"></i> Students</a></li>
                    <li><a href="/student_portal/teacher/grades.php" class="active"><i class="fas fa-chart-bar"></i> Grades</a></li>
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
                    <h2>Manage Grades</h2>
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
                                <h3 class="card-title">Add Grade</h3>
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
                                        <label for="student_id">Student</label>
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
                                        <label for="subject">Subject</label>
                                        <input type="text" id="subject" name="subject" placeholder="e.g., Mathematics" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="grade">Grade (0-100)</label>
                                        <input type="number" id="grade" name="grade" min="0" max="100" step="0.01" placeholder="Enter grade" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="exam_type">Exam Type</label>
                                        <select id="exam_type" name="exam_type" required>
                                            <option value="">-- Select Type --</option>
                                            <option value="Midterm">Midterm</option>
                                            <option value="Final">Final</option>
                                            <option value="Quiz">Quiz</option>
                                            <option value="Assignment">Assignment</option>
                                            <option value="Project">Project</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                                        <i class="fas fa-plus-circle"></i> Add Grade
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Information -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Grading Information</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <div>
                                        <strong>Grade Scale:</strong>
                                        <ul style="margin: 0.5rem 0; padding-left: 1.5rem;">
                                            <li>90-100: Excellent</li>
                                            <li>80-89: Very Good</li>
                                            <li>70-79: Good</li>
                                            <li>60-69: Satisfactory</li>
                                            <li>Below 60: Needs Improvement</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <strong>Exam Types:</strong>
                                        <ul style="margin: 0.5rem 0; padding-left: 1.5rem;">
                                            <li>Midterm: Mid-semester exam</li>
                                            <li>Final: End-of-semester exam</li>
                                            <li>Quiz: Short assessment</li>
                                            <li>Assignment: Homework or project</li>
                                        </ul>
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

