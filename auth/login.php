<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields';
        redirect('../index.php');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, username, email, password, role FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect to main dashboard for all roles
            redirect('../dashboard.php');
        } else {
            $_SESSION['error'] = 'Invalid password';
            redirect('../index.php');
        }
    } else {
        $_SESSION['error'] = 'User not found';
        redirect('../index.php');
    }
} else {
    redirect('../index.php');
}
?>

