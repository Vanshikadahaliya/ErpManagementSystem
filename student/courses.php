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
    <title>My Courses - College ERP</title>
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
                <li><a href="courses.php" class="active"><i class="fas fa-book"></i> My Courses</a></li>
                <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="grades.php"><i class="fas fa-chart-line"></i> Grades</a></li>
                <li><a href="notices.php"><i class="fas fa-bullhorn"></i> Notices</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>My Courses</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($username); ?> (Student)</span>
                    <a href="../auth/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Courses Content -->
            <div class="card">
                <div class="card-header">
                    <h3>Enrolled Courses</h3>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Instructor</th>
                                <th>Schedule</th>
                                <th>Room</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CS101</td>
                                <td>Introduction to Programming</td>
                                <td>Dr. Smith</td>
                                <td>Mon, Wed, Fri 9:00 AM</td>
                                <td>Room 101</td>
                                <td>
                                    <button class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>CS201</td>
                                <td>Data Structures</td>
                                <td>Dr. Johnson</td>
                                <td>Tue, Thu 2:00 PM</td>
                                <td>Room 205</td>
                                <td>
                                    <button class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                </td>
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
