<?php
/**
 * Database Configuration File
 * Student Portal System
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_portal');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    // Database created or already exists
}

// Select database
$conn->select_db(DB_NAME);

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Create tables if they don't exist
$tables = array(
    // Users table
    "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        full_name VARCHAR(150) NOT NULL,
        role ENUM('admin', 'teacher', 'counselor', 'parent', 'student') NOT NULL,
        phone VARCHAR(20),
        profile_picture VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active TINYINT DEFAULT 1
    )",
    
    // Students table
    "CREATE TABLE IF NOT EXISTS students (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        student_id VARCHAR(50) UNIQUE NOT NULL,
        date_of_birth DATE,
        gender ENUM('Male', 'Female') NOT NULL,
        class_name VARCHAR(50),
        guardian_id INT,
        health_info TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (guardian_id) REFERENCES users(id)
    )",
    
    // Guardian (Parent) information
    "CREATE TABLE IF NOT EXISTS guardians (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        relationship VARCHAR(50),
        occupation VARCHAR(100),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    // Teachers table
    "CREATE TABLE IF NOT EXISTS teachers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        subject VARCHAR(100),
        class_assigned VARCHAR(50),
        specialization VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    // Notes/Observations table
    "CREATE TABLE IF NOT EXISTS notes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        teacher_id INT NOT NULL,
        note_type ENUM('academic', 'behavioral', 'positive', 'warning') NOT NULL,
        subject VARCHAR(100),
        details TEXT NOT NULL,
        importance_level ENUM('low', 'medium', 'high') DEFAULT 'medium',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id)
    )",
    
    // Grades table
    "CREATE TABLE IF NOT EXISTS grades (
        id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        subject VARCHAR(100),
        grade DECIMAL(5, 2),
        exam_type VARCHAR(50),
        date_recorded DATE,
        teacher_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id)
    )",
    
    // Attendance table
    "CREATE TABLE IF NOT EXISTS attendance (
        id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        attendance_date DATE,
        status ENUM('present', 'absent', 'late') DEFAULT 'present',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )",
    
    // Messages/Chat table
    "CREATE TABLE IF NOT EXISTS messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        attachment_path VARCHAR(255),
        is_read TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    // Notifications table
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(255),
        message TEXT,
        type VARCHAR(50),
        related_id INT,
        is_read TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    // Counseling Sessions table
    "CREATE TABLE IF NOT EXISTS counseling_sessions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        counselor_id INT NOT NULL,
        session_date DATETIME,
        status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
        treatment_plan TEXT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (counselor_id) REFERENCES users(id)
    )",
    
    // Special Circumstances table
    "CREATE TABLE IF NOT EXISTS special_circumstances (
        id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        description TEXT NOT NULL,
        confidential_notes TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )",
    
    // Class Announcements table
    "CREATE TABLE IF NOT EXISTS announcements (
        id INT PRIMARY KEY AUTO_INCREMENT,
        class_name VARCHAR(50),
        title VARCHAR(255),
        content TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )"
);

// Execute table creation queries
foreach ($tables as $table) {
    if ($conn->query($table) === FALSE) {
        // Log error but don't stop execution
        error_log("Error creating table: " . $conn->error);
    }
}

?>

