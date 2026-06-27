<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    switch ($role) {
        case 'admin':
            header("Location: /student_portal/admin/dashboard.php");
            break;
        case 'teacher':
            header("Location: /student_portal/teacher/dashboard.php");
            break;
        case 'counselor':
            header("Location: /student_portal/counselor/dashboard.php");
            break;
        case 'parent':
            header("Location: /student_portal/parent/dashboard.php");
            break;
        default:
            header("Location: /student_portal/login.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Comprehensive Student Follow-up System</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
    <style>
        .landing-page {
            background: linear-gradient(135deg, #3B82F6 0%, #A78BFA 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .landing-content {
            text-align: center;
            max-width: 800px;
            padding: 2rem;
        }

        .landing-logo {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .landing-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .landing-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            line-height: 1.8;
        }

        .landing-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-landing {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            border: 2px solid white;
        }

        .btn-login {
            background-color: white;
            color: #3B82F6;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-signup {
            background-color: transparent;
            color: white;
        }

        .btn-signup:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
            padding: 2rem;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .feature {
            text-align: center;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .feature-desc {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .footer-landing {
            position: absolute;
            bottom: 2rem;
            text-align: center;
            width: 100%;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="landing-page">
        <div class="landing-content">
        <div class="landing-logo">
            <i class="fa-solid fa-user-friends"></i>
            <i class="fa-solid fa-comment-dots" style="font-size:1.5rem; position: relative; left:-10px; top:-20px;"></i>
        </div>

            <h1 class="landing-title">Student Portal</h1>
            
            <p class="landing-subtitle">
                A comprehensive follow-up system for students  communication, reports, and notifications all in one place.
            </p>

            <div class="landing-buttons">
                <a href="/student_portal/login.php" class="btn-landing btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="/student_portal/register.php" class="btn-landing btn-signup">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
            </div>

            <div class="features">
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="feature-title">Student Profile</div>
                    <div class="feature-desc">Complete electronic profile with academic, behavioral, and health information</div>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="feature-title">Notifications</div>
                    <div class="feature-desc">Instant notifications for notes, messages, and important updates</div>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="feature-title">Direct Communication</div>
                    <div class="feature-desc">Secure messaging between parents, teachers, and counselors</div>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="feature-title">Reports & Analytics</div>
                    <div class="feature-desc">Comprehensive reports and statistics on student performance</div>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="feature-title">Secure & Private</div>
                    <div class="feature-desc">Role-based access control and encrypted communication</div>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="feature-title">Responsive Design</div>
                    <div class="feature-desc">Works seamlessly on desktop, tablet, and mobile devices</div>
                </div>
            </div>
        </div>

        <div class="footer-landing">
            <p>&copy; 2024 Student Portal System. All rights reserved.</p>
        </div>
    </div>

    <script src="/student_portal/assets/js/main.js"></script>
</body>
</html>

