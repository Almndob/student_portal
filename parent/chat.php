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

// جلب قائمة جهات الاتصال (معلم / مرشد)
$contacts_query = "SELECT DISTINCT u.id, u.full_name, u.role 
                   FROM users u 
                   WHERE u.role IN ('teacher', 'counselor') 
                   ORDER BY u.full_name";
$contacts_stmt = $conn->prepare($contacts_query);
$contacts_stmt->execute();
$contacts_result = $contacts_stmt->get_result();
$contacts = [];
while ($row = $contacts_result->fetch_assoc()) {
    $contacts[] = $row;
}

// جهة الاتصال المحددة
$selected_contact_id = isset($_GET['contact_id']) ? (int)$_GET['contact_id'] : 0;
$selected_contact = null;
$messages = [];

if ($selected_contact_id > 0) {
    foreach ($contacts as $contact) {
        if ($contact['id'] == $selected_contact_id) {
            $selected_contact = $contact;
            break;
        }
    }

    if ($selected_contact) {
        $messages_query = "SELECT m.*, u.full_name, u.role 
                           FROM messages m 
                           JOIN users u ON m.sender_id = u.id 
                           WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                              OR (m.sender_id = ? AND m.receiver_id = ?)
                           ORDER BY m.created_at ASC";
        $messages_stmt = $conn->prepare($messages_query);
        $messages_stmt->bind_param("iiii", $user_id, $selected_contact_id, $selected_contact_id, $user_id);
        $messages_stmt->execute();
        $messages_result = $messages_stmt->get_result();
        while ($row = $messages_result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
}


// Handle message sending
$send_error = '';
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
//     $receiver_id = (int)($_POST['receiver_id'] ?? 0);
//     $message_text = sanitize($_POST['message_text'] ?? '');

//     if ($receiver_id <= 0 || empty($message_text)) {
//         $send_error = 'Please select a contact and enter a message';
//     } else {
//        $insert_query = "INSERT INTO messages (sender_id, receiver_id, message, created_at) 
//                  VALUES (?, ?, ?, NOW())";
// $insert_stmt = $conn->prepare($insert_query);
// $insert_stmt->bind_param("iis", $user_id, $receiver_id, $message_text);

        
//         if ($insert_stmt->execute()) {
//             // Create notification for receiver
//             createNotification($receiver_id, 'New Message', "You have a new message from $full_name", 'message', $user_id, $conn);
            
//             // Redirect to refresh messages
//            // header("Location: /student_portal/parent/chat.php?contact_id=$receiver_id");
//             exit();
//         } else {
//             $send_error = 'Error sending message';
//         }
//     }
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Student Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .chat-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 1rem;
            height: calc(100vh - 200px);
        }

        .contacts-list {
            background-color: var(--background-color);
            border-radius: var(--border-radius);
            overflow-y: auto;
            border: 1px solid var(--border-color);
        }

        .contact-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: var(--transition);
        }

        .contact-item:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }

        .contact-item.active {
            background-color: var(--primary-color);
            color: white;
        }

        .chat-area {
            display: flex;
            flex-direction: column;
            background-color: var(--background-color);
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .chat-header {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            background-color: white;
        }

        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .message.sent {
            justify-content: flex-end;
        }

        .message.received {
            justify-content: flex-start;
        }

        .message-content {
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            word-wrap: break-word;
        }

        .message.sent .message-content {
            background-color: var(--primary-color);
            color: white;
        }

        .message.received .message-content {
            background-color: #E5E7EB;
            color: var(--text-color);
        }

        .message-time {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }

        .chat-input-area {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
            background-color: white;
            display: flex;
            gap: 0.5rem;
        }

        .chat-input-area textarea {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: inherit;
            resize: none;
            max-height: 100px;
        }

        .chat-input-area button {
            padding: 0.75rem 1.5rem;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .chat-container {
                grid-template-columns: 1fr;
            }

            .contacts-list {
                display: none;
            }
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
                    <li><a href="/student_portal/parent/chat.php" class="active"><i class="fas fa-comments"></i> Chat</a></li>
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
                    <h2>Direct Messages</h2>
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
                    <div class="chat-container">
                        <!-- Contacts List -->
                        <div class="contacts-list">
                            <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); font-weight: 600;">
                                <i class="fas fa-users"></i> Contacts
                            </div>
                            <?php if (!empty($contacts)): ?>
                                <?php foreach ($contacts as $contact): ?>
                                    <div class="contact-item <?php echo $contact['id'] == $selected_contact_id ? 'active' : ''; ?>"
                                         onclick="location.href='/student_portal/parent/chat.php?contact_id=<?php echo $contact['id']; ?>'">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: <?php echo $contact['id'] == $selected_contact_id ? 'rgba(255,255,255,0.3)' : 'var(--primary-color)'; ?>; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; flex-shrink: 0;">
                                                <?php echo strtoupper(substr($contact['full_name'], 0, 1)); ?>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    <?php echo htmlspecialchars($contact['full_name']); ?>
                                                </div>
                                                <small style="opacity: 0.7;">
                                                    <?php echo ucfirst($contact['role']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="padding: 1rem; text-align: center; color: var(--text-light);">
                                    No contacts available
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Chat Area -->
                        <?php if ($selected_contact): ?>
                            <div class="chat-area">
                                <!-- Chat Header -->
                                <div class="chat-header">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                            <?php echo strtoupper(substr($selected_contact['full_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h4 style="margin: 0;"><?php echo htmlspecialchars($selected_contact['full_name']); ?></h4>
                                            <small style="color: var(--text-light);"><?php echo ucfirst($selected_contact['role']); ?></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Messages Area -->
                                <div class="messages-area" id="messagesArea" data-receiver-id="<?php echo $selected_contact['id'] ?? ''; ?>">
                                    <?php if (!empty($messages)): ?>
                                        <?php foreach ($messages as $msg): ?>
                                            <div class="message <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                                                <div>
                                                    <div class="message-content">
                                                        <?php echo htmlspecialchars($msg['message']); ?>
                                                    </div>
                                                    <div class="message-time">
                                                        <?php echo formatDateTime($msg['created_at']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div style="text-align: center; color: var(--text-light); margin: auto;">
                                            <i class="fas fa-comments" style="font-size: 2rem; opacity: 0.5; margin-bottom: 1rem;"></i>
                                            <p>No messages yet. Start the conversation!</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Chat Input -->
                                <div class="chat-input-area">
                                  <form class="message-form" style="display: flex; gap: 0.5rem; width: 100%;">
                                        <input type="hidden" name="receiver_id" value="<?php echo $selected_contact['id']; ?>">
                                        <textarea name="message_text" placeholder="Type your message..." required></textarea>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                                    </form>

                                </div>
                            </div>
                        <?php else: ?>
                            <div class="chat-area">
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
                                    <div style="text-align: center; color: var(--text-light);">
                                        <i class="fas fa-comments" style="font-size: 3rem; opacity: 0.5; margin-bottom: 1rem;"></i>
                                        <p>Select a contact to start chatting</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
     

        // Auto-scroll to bottom of messages
        const messagesArea = document.getElementById('messagesArea');
        if (messagesArea) {
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }
    </script>
    <script src="/student_portal/assets/js/main.js"></script>
</body>
</html>

