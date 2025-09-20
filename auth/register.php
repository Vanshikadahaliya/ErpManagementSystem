<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitizeInput($_POST['role']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $_SESSION['error'] = 'Please fill in all fields';
        redirect('../register.php');
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match';
        redirect('../register.php');
    }
    
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters long';
        redirect('../register.php');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format';
        redirect('../register.php');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if username or email already exists
    $check_query = "SELECT id FROM users WHERE username = :username OR email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':username', $username);
    $check_stmt->bindParam(':email', $email);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = 'Username or email already exists';
        redirect('../register.php');
    }
    
    // Hash password and insert user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $insert_query = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':username', $username);
    $insert_stmt->bindParam(':email', $email);
    $insert_stmt->bindParam(':password', $hashed_password);
    $insert_stmt->bindParam(':role', $role);
    
    if ($insert_stmt->execute()) {
        $_SESSION['success'] = 'Registration successful! Please login.';
        redirect('../index.php');
    } else {
        $_SESSION['error'] = 'Registration failed. Please try again.';
        redirect('../register.php');
    }
} else {
    redirect('../register.php');
}
?>
