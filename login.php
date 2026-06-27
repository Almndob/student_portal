<?php
session_start();
require_once 'includes/db_config.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Query database for user
        $query = "SELECT * FROM users WHERE username = ? AND is_active = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (verifyPassword($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['profile_picture'] = $user['profile_picture'];

                // Redirect based on role
                switch ($user['role']) {
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
                        header("Location: /student_portal/index.php");
                }
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            display: flex;
            height: 100vh;
            background: linear-gradient(135deg, #3B82F6 0%, #A78BFA 100%);
        }

        .login-left {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 2rem;
        }

        .login-left-content {
            text-align: center;
            max-width: 400px;
        }

        .login-left-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
        }

        .login-left-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .login-left-text {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.8;
        }

        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: white;
        }

        .login-form-container {
            width: 100%;
            max-width: 400px;
        }

        .login-form-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .login-form-subtitle {
            color: var(--text-light);
            margin-bottom: 2rem;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group-login {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group-login label {
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group-login input {
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group-login input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }

        .remember-forgot a {
            color: var(--primary-color);
        }

        .login-button {
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .login-button:hover {
            background-color: #2563EB;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .login-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-light);
        }

        .signup-link a {
            color: var(--primary-color);
            font-weight: 600;
        }

        .alert-login {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-left {
                min-height: 40vh;
            }

            .login-right {
                min-height: 60vh;
            }

            .login-left-title {
                font-size: 1.75rem;
            }

            .login-left-icon {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="login-left-content">
                <div class="login-left-icon">
                      <div class="landing-logo">
                            <i class="fa-solid fa-user-friends"></i>
                            <i class="fa-solid fa-comment-dots" style="font-size:1.5rem; position: relative; left:-10px; top:-20px;"></i>
                        </div>
                </div>
                <h1 class="login-left-title">Student Portal</h1>
                <p class="login-left-text">Comprehensive student follow-up system for seamless communication and performance tracking.</p>
            </div>
        </div>

        <div class="login-right">
            <div class="login-form-container">
                <h2 class="login-form-title">Welcome Back</h2>
                <p class="login-form-subtitle">Sign in to your account</p>

                <?php if (!empty($error)): ?>
                    <div class="alert-login alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert-login alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <div class="form-group-login">
                        <label for="username">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    </div>

                    <div class="form-group-login">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <div class="remember-forgot">
                        <label>
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="/student_portal/forgot_password.php">Forgot password?</a>
                    </div>

                    <button type="submit" class="login-button">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="signup-link">
                    Don't have an account? <a href="/student_portal/register.php">Create one</a>
                </div>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color); text-align: center; color: var(--text-light); font-size: 0.9rem;">
                    <p><strong>Demo Credentials:</strong></p>
                    <p>Admin: admin / 123456</p>
                    <p>Teacher: teacher1 / 123456</p>
                    <p>Parent: parent1 / 123456</p>
                </div>
            </div>
        </div>
    </div>

    <script src="/student_portal/assets/js/main.js"></script>
</body>
</html>

