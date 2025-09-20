<?php
require_once '../config/database.php';

// Check if user is logged in and is faculty
if (!isLoggedIn() || $_SESSION['role'] !== 'faculty') {
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
    <title>My Students - College ERP</title>
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
                <li><a href="students.php" class="active"><i class="fas fa-user-graduate"></i> My Students</a></li>
                <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="grades.php"><i class="fas fa-chart-line"></i> Grades</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>My Students</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($username); ?> (Faculty)</span>
                    <a href="../auth/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Students Content -->
            <div class="card">
                <div class="card-header">
                    <h3>Students in My Courses</h3>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Attendance %</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>STU001</td>
                                <td>John Doe</td>
                                <td>john.doe@college.edu</td>
                                <td>CS101 - Introduction to Programming</td>
                                <td>85%</td>
                                <td>
                                    <button class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>STU002</td>
                                <td>Jane Smith</td>
                                <td>jane.smith@college.edu</td>
                                <td>CS101 - Introduction to Programming</td>
                                <td>92%</td>
                                <td>
                                    <button class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
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
