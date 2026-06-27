<?php
session_start();
require_once 'includes/db_config.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'parent');

    // Validation
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'All fields are required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else {
        // Check if username or email already exists
        $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            // Insert new user
            $hashed_password = hashPassword($password);
            $insert_query = "INSERT INTO users (username, email, full_name, password, role) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sssss", $username, $email, $full_name, $hashed_password, $role);

            if ($insert_stmt->execute()) {
                $success = 'Account created successfully! Please log in.';
                // Redirect to login after 2 seconds
                header("Refresh: 2; url=/student_portal/login.php");
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Portal</title>
    <link rel="stylesheet" href="/student_portal/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .register-container {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, #3B82F6 0%, #A78BFA 100%);
            padding: 2rem;
        }

        .register-content {
            width: 100%;
            max-width: 500px;
            margin: auto;
            background-color: white;
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--shadow-lg);
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .register-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .register-subtitle {
            color: var(--text-light);
        }

        .register-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group-register {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group-register label {
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group-register input,
        .form-group-register select {
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group-register input:focus,
        .form-group-register select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .password-strength {
            display: flex;
            gap: 0.25rem;
            margin-top: 0.5rem;
        }

        .strength-bar {
            flex: 1;
            height: 4px;
            background-color: var(--border-color);
            border-radius: 2px;
        }

        .strength-bar.weak {
            background-color: var(--danger-color);
        }

        .strength-bar.medium {
            background-color: var(--warning-color);
        }

        .strength-bar.strong {
            background-color: var(--success-color);
        }

        .register-button {
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

        .register-button:hover {
            background-color: #2563EB;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-light);
        }

        .login-link a {
            color: var(--primary-color);
            font-weight: 600;
        }

        .alert-register {
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

        @media (max-width: 600px) {
            .register-content {
                padding: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .register-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-content">
            <div class="register-header">
                <div class="register-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1 class="register-title">Create Account</h1>
                <p class="register-subtitle">Join Student Portal today</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-register alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert-register alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="register-form">
                <div class="form-group-register">
                    <label for="full_name">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" required>
                </div>

                <div class="form-row">
                    <div class="form-group-register">
                        <label for="username">
                            <i class="fas fa-at"></i> Username
                        </label>
                        <input type="text" id="username" name="username" placeholder="Choose username" required>
                    </div>

                    <div class="form-group-register">
                        <label for="role">
                            <i class="fas fa-briefcase"></i> Role
                        </label>
                        <select id="role" name="role" required>
                            <option value="parent">Parent</option>
                            <option value="teacher">Teacher</option>
                            <option value="counselor">Counselor</option>
                        </select>
                    </div>
                </div>

                <div class="form-group-register">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="form-row">
                    <div class="form-group-register">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" id="password" name="password" placeholder="Create password" required>
                        <div class="password-strength">
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                        </div>
                    </div>

                    <div class="form-group-register">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirm Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                    </div>
                </div>

                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--text-light);">
                    <input type="checkbox" required> I agree to the Terms of Service and Privacy Policy
                </label>

                <button type="submit" class="register-button">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="login-link">
                Already have an account? <a href="/student_portal/login.php">Sign in</a>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBars = document.querySelectorAll('.strength-bar');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            strengthBars.forEach((bar, index) => {
                bar.classList.remove('weak', 'medium', 'strong');
                if (index < strength) {
                    if (strength <= 2) bar.classList.add('weak');
                    else if (strength === 3) bar.classList.add('medium');
                    else bar.classList.add('strong');
                }
            });
        });
    </script>
</body>
</html>

