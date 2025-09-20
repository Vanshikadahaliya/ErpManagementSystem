<?php
require_once '../config/database.php';

// Check if user is logged in and is student
if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    redirect('../index.php');
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - College ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-graduation-cap"></i> College ERP</h2>
                <p>Welcome, <?php echo htmlspecialchars($username); ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="courses.php"><i class="fas fa-book"></i> My Courses</a></li>
                <li><a href="attendance.php" class="active"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="grades.php"><i class="fas fa-chart-line"></i> Grades</a></li>
                <li><a href="notices.php"><i class="fas fa-bullhorn"></i> Notices</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>My Attendance</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($username); ?> (Student)</span>
                    <a href="../auth/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Attendance Content -->
            <div class="card">
                <div class="card-header">
                    <h3>Attendance Record</h3>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Course</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Sep 19, 2025</td>
                                <td>CS101 - Introduction to Programming</td>
                                <td><span class="badge badge-success">Present</span></td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>Sep 18, 2025</td>
                                <td>CS101 - Introduction to Programming</td>
                                <td><span class="badge badge-success">Present</span></td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>Sep 17, 2025</td>
                                <td>CS201 - Data Structures</td>
                                <td><span class="badge badge-danger">Absent</span></td>
                                <td>Late arrival</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
