<?php
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../index.php');
}

$user_role = $_SESSION['role'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices - College ERP</title>
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
                
                <?php if ($user_role === 'admin'): ?>
                <li><a href="../students/index.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="../faculty/index.php"><i class="fas fa-chalkboard-teacher"></i> Faculty</a></li>
                <li><a href="../courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="../departments/index.php"><i class="fas fa-building"></i> Departments</a></li>
                <li><a href="../attendance/index.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="../grades/index.php"><i class="fas fa-chart-line"></i> Grades</a></li>
                <li><a href="index.php" class="active"><i class="fas fa-bullhorn"></i> Notices</a></li>
                <?php endif; ?>
                
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Notices</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($username); ?> (<?php echo ucfirst($user_role); ?>)</span>
                    <a href="../auth/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Notices Content -->
            <div class="card">
                <div class="card-header">
                    <h3>College Notices</h3>
                    <?php if ($user_role === 'admin'): ?>
                    <button class="btn btn-primary" onclick="showAddNoticeForm()">
                        <i class="fas fa-plus"></i> Add Notice
                    </button>
                    <?php endif; ?>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Priority</th>
                                <?php if ($user_role === 'admin'): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Sep 19, 2025</td>
                                <td>Welcome to New Academic Year</td>
                                <td>Welcome all students to the new academic year 2025-26. Classes will begin from October 1st.</td>
                                <td><span class="badge badge-info">Normal</span></td>
                                <?php if ($user_role === 'admin'): ?>
                                <td>
                                    <button class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <tr>
                                <td>Sep 18, 2025</td>
                                <td>Library Hours Update</td>
                                <td>The library will now be open from 8 AM to 10 PM on weekdays and 9 AM to 6 PM on weekends.</td>
                                <td><span class="badge badge-success">Low</span></td>
                                <?php if ($user_role === 'admin'): ?>
                                <td>
                                    <button class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <tr>
                                <td>Sep 17, 2025</td>
                                <td>Exam Schedule Released</td>
                                <td>Mid-term examination schedule has been released. Please check the notice board for details.</td>
                                <td><span class="badge badge-danger">High</span></td>
                                <?php if ($user_role === 'admin'): ?>
                                <td>
                                    <button class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        function showAddNoticeForm() {
            alert('Add Notice functionality will be implemented here.');
        }
    </script>
</body>
</html>
