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

// Handle session creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_session'])) {
    $student_id = $_POST['student_id'];
    $session_date = $_POST['session_date'];
    $treatment_plan = $_POST['treatment_plan'];
    
    $insert_query = "INSERT INTO counseling_sessions (student_id, counselor_id, session_date, treatment_plan, status) 
                     VALUES (?, ?, ?, ?, 'scheduled')";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iiss", $student_id, $user_id, $session_date, $treatment_plan);
    
    if ($insert_stmt->execute()) {
        $success_message = "Session scheduled successfully!";
    } else {
        $error_message = "Error scheduling session. Please try again.";
    }
}

// Handle session status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $session_id = $_POST['session_id'];
    $new_status = $_POST['status'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : null;
    
    $update_query = "UPDATE counseling_sessions SET status = ?, notes = ? WHERE id = ? AND counselor_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssii", $new_status, $notes, $session_id, $user_id);
    
    if ($update_stmt->execute()) {
        $success_message = "Session updated successfully!";
    } else {
        $error_message = "Error updating session. Please try again.";
    }
}

// Get all sessions
$sessions_query = "SELECT cs.*, s.id as student_id, u.full_name as student_name, s.class_name 
                   FROM counseling_sessions cs
                   JOIN students s ON cs.student_id = s.id
                   JOIN users u ON s.user_id = u.id
                   WHERE cs.counselor_id = ?
                   ORDER BY cs.session_date DESC";
$sessions_stmt = $conn->prepare($sessions_query);
$sessions_stmt->bind_param("i", $user_id);
$sessions_stmt->execute();
$sessions_result = $sessions_stmt->get_result();
$sessions = [];
while ($row = $sessions_result->fetch_assoc()) {
    $sessions[] = $row;
}

// Get all students for the dropdown
$students_query = "SELECT s.id, u.full_name, s.class_name 
                   FROM students s
                   JOIN users u ON s.user_id = u.id
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
    <title>Sessions - Counselor Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 2rem;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            border-radius: var(--border-radius);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #000;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .session-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1rem;
        }
        .session-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        .session-info h3 {
            margin: 0 0 0.5rem 0;
        }
        .session-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
        }
        .session-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            background: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
        }
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
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
                    <li><a href="/student_portal/counselor/students.php"><i class="fas fa-users"></i> Students</a></li>
                    <li><a href="/student_portal/counselor/sessions.php" class="active"><i class="fas fa-calendar"></i> Sessions</a></li>
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
                    <h2>Counseling Sessions</h2>
                </div>
                <div class="header-right">
                    <button class="btn btn-primary" onclick="openModal()">
                        <i class="fas fa-plus-circle"></i> New Session
                    </button>
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
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <!-- Filter Buttons -->
                    <div class="filter-buttons">
                        <button class="filter-btn active" onclick="filterSessions('all')">All Sessions</button>
                        <button class="filter-btn" onclick="filterSessions('scheduled')">Scheduled</button>
                        <button class="filter-btn" onclick="filterSessions('completed')">Completed</button>
                        <button class="filter-btn" onclick="filterSessions('cancelled')">Cancelled</button>
                    </div>

                    <!-- Sessions List -->
                    <div id="sessionsList">
                        <?php if (!empty($sessions)): ?>
                            <?php foreach ($sessions as $session): ?>
                                <div class="session-card" data-status="<?php echo $session['status']; ?>">
                                    <div class="session-header">
                                        <div class="session-info">
                                            <h3><?php echo htmlspecialchars($session['student_name']); ?></h3>
                                            <small style="color: var(--text-light);">Class: <?php echo htmlspecialchars($session['class_name']); ?></small>
                                        </div>
                                        <span class="badge badge-<?php echo $session['status'] === 'completed' ? 'success' : ($session['status'] === 'scheduled' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($session['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="session-details">
                                        <div class="detail-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo formatDateTime($session['session_date']); ?></span>
                                        </div>
                                    </div>

                                    <?php if ($session['treatment_plan']): ?>
                                        <div style="margin-bottom: 1rem;">
                                            <strong>Treatment Plan:</strong>
                                            <p style="margin: 0.5rem 0; color: var(--text-light);"><?php echo htmlspecialchars($session['treatment_plan']); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($session['notes']): ?>
                                        <div style="margin-bottom: 1rem;">
                                            <strong>Notes:</strong>
                                            <p style="margin: 0.5rem 0; color: var(--text-light);"><?php echo htmlspecialchars($session['notes']); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($session['status'] === 'scheduled'): ?>
                                        <div class="session-actions">
                                            <button class="btn btn-success" onclick="updateSessionStatus(<?php echo $session['id']; ?>, 'completed')">
                                                <i class="fas fa-check"></i> Mark Complete
                                            </button>
                                            <button class="btn btn-danger" onclick="updateSessionStatus(<?php echo $session['id']; ?>, 'cancelled')">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="card">
                                <div class="card-body" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-calendar-times" style="font-size: 4rem; color: var(--text-light); opacity: 0.3; margin-bottom: 1rem;"></i>
                                    <p style="color: var(--text-light); font-size: 1.1rem;">No sessions found</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for New Session -->
    <div id="sessionModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 style="margin-bottom: 1.5rem;">Schedule New Session</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="student_id">Student *</label>
                    <select id="student_id" name="student_id" required>
                        <option value="">Select a student</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>">
                                <?php echo htmlspecialchars($student['full_name']) . ' - ' . htmlspecialchars($student['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="session_date">Session Date & Time *</label>
                    <input type="datetime-local" id="session_date" name="session_date" required>
                </div>
                

                
                <div class="form-group">
                    <label for="treatment_plan">Treatment Plan</label>
                    <textarea id="treatment_plan" name="treatment_plan" placeholder="Enter treatment plan details..."></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                    <button type="submit" name="create_session" class="btn btn-primary">Schedule Session</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden form for status updates -->
    <form id="statusUpdateForm" method="POST" action="" style="display: none;">
        <input type="hidden" name="session_id" id="status_session_id">
        <input type="hidden" name="status" id="status_value">
        <input type="hidden" name="update_status" value="1">
    </form>

    <script>
        function openModal() {
            document.getElementById('sessionModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('sessionModal').style.display = 'none';
        }

        function filterSessions(status) {
            const cards = document.querySelectorAll('.session-card');
            const buttons = document.querySelectorAll('.filter-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            cards.forEach(card => {
                if (status === 'all' || card.getAttribute('data-status') === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function updateSessionStatus(sessionId, status) {
            if (confirm('Are you sure you want to update this session status?')) {
                document.getElementById('status_session_id').value = sessionId;
                document.getElementById('status_value').value = status;
                document.getElementById('statusUpdateForm').submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('sessionModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
    <script src="/student_portal/assets/js/main.js"></script>
</body>
</html>
