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

// Get student information
$student_query = "SELECT s.*, u.full_name FROM students s 
                  JOIN users u ON s.user_id = u.id 
                  WHERE s.guardian_id = ?";
$student_stmt = $conn->prepare($student_query);
$student_stmt->bind_param("i", $user_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student = $student_result->fetch_assoc();

if (!$student) {
    $student = [];
}

$student_id = $student['id'] ?? 0;

// Get all notes
$notes = getStudentNotes($student_id, $conn, 100);

// Filter notes by type
$filter = $_GET['filter'] ?? 'all';
$filtered_notes = $notes;

if ($filter !== 'all') {
    $filtered_notes = array_filter($notes, function($n) use ($filter) {
        return $n['note_type'] === $filter;
    });
}

// Count notes by type
$academic_count = count(array_filter($notes, function($n) { return $n['note_type'] === 'academic'; }));
$behavioral_count = count(array_filter($notes, function($n) { return $n['note_type'] === 'behavioral'; }));
$positive_count = count(array_filter($notes, function($n) { return $n['note_type'] === 'positive'; }));
$warning_count = count(array_filter($notes, function($n) { return $n['note_type'] === 'warning'; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes - Student Portal</title>
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
                    <li><a href="/student_portal/parent/notes.php" class="active"><i class="fas fa-sticky-note"></i> Notes</a></li>
                    <li><a href="/student_portal/parent/chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                    <li><a href="/student_portal/parent/notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
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
                    <h2>Notes & Observations</h2>
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
                    <!-- Filter Buttons -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-body">
                            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                <a href="/student_portal/parent/notes.php?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">
                                    <i class="fas fa-list"></i> All (<?php echo count($notes); ?>)
                                </a>
                                <a href="/student_portal/parent/notes.php?filter=academic" class="btn <?php echo $filter === 'academic' ? 'btn-primary' : 'btn-outline'; ?>">
                                    <i class="fas fa-book"></i> Academic (<?php echo $academic_count; ?>)
                                </a>
                                <a href="/student_portal/parent/notes.php?filter=behavioral" class="btn <?php echo $filter === 'behavioral' ? 'btn-primary' : 'btn-outline'; ?>">
                                    <i class="fas fa-heart"></i> Behavioral (<?php echo $behavioral_count; ?>)
                                </a>
                                <a href="/student_portal/parent/notes.php?filter=positive" class="btn <?php echo $filter === 'positive' ? 'btn-primary' : 'btn-outline'; ?>">
                                    <i class="fas fa-star"></i> Positive (<?php echo $positive_count; ?>)
                                </a>
                                <a href="/student_portal/parent/notes.php?filter=warning" class="btn <?php echo $filter === 'warning' ? 'btn-primary' : 'btn-outline'; ?>">
                                    <i class="fas fa-exclamation-triangle"></i> Warning (<?php echo $warning_count; ?>)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Notes List -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Notes History</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($filtered_notes)): ?>
                                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                                    <?php foreach ($filtered_notes as $note): ?>
                                        <div style="padding: 1.5rem; background-color: var(--background-color); border-radius: var(--border-radius); border-left: 5px solid 
                                            <?php 
                                                echo $note['note_type'] === 'academic' ? 'var(--primary-color)' :
                                                     ($note['note_type'] === 'behavioral' ? 'var(--warning-color)' :
                                                      ($note['note_type'] === 'positive' ? 'var(--success-color)' : 'var(--danger-color)'));
                                            ?>;">
                                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                                <div>
                                                    <h4 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($note['subject']); ?></h4>
                                                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                                                        <span class="badge badge-<?php echo $note['note_type']; ?>">
                                                            <i class="fas fa-tag"></i> <?php echo ucfirst($note['note_type']); ?>
                                                        </span>
                                                        <span class="badge badge-<?php 
                                                            echo $note['importance_level'] === 'high' ? 'danger' : 
                                                                 ($note['importance_level'] === 'medium' ? 'warning' : 'primary');
                                                        ?>">
                                                            <i class="fas fa-flag"></i> <?php echo ucfirst($note['importance_level']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <small style="color: var(--text-light); white-space: nowrap;">
                                                    <?php echo formatDateTime($note['created_at']); ?>
                                                </small>
                                            </div>

                                            <p style="margin: 1rem 0; color: var(--text-color); line-height: 1.6;">
                                                <?php echo htmlspecialchars($note['details']); ?>
                                            </p>

                                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                                <small style="color: var(--text-light);">
                                                    <i class="fas fa-user"></i> By: <?php echo htmlspecialchars($note['teacher_name']); ?>
                                                </small>
                                                <a href="/student_portal/parent/chat.php" class="btn btn-sm btn-outline">
                                                    <i class="fas fa-reply"></i> Reply
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div style="text-align: center; padding: 3rem; color: var(--text-light);">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                    <p>No notes available for this filter</p>
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

