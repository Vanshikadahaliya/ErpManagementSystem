<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php');
}

// Get user info
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$username = $_SESSION['username'];

// Get dashboard statistics
$database = new Database();
$db = $database->getConnection();

// Get counts based on user role
$stats = [];
$queries = []; // Initialize queries array

if ($user_role === 'admin') {
    // Admin sees all statistics
    $queries = [
        'students' => "SELECT COUNT(*) as count FROM students WHERE status = 'Active'",
        'faculty' => "SELECT COUNT(*) as count FROM faculty WHERE status = 'Active'",
        'courses' => "SELECT COUNT(*) as count FROM courses WHERE status = 'Active'",
        'attendance_rate' => "SELECT ROUND(AVG(CASE WHEN status = 'Present' THEN 100 ELSE 0 END), 1) as rate FROM attendance WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
    ];
} elseif ($user_role === 'faculty') {
    // Faculty sees their course statistics
    $faculty_id = $db->prepare("SELECT id FROM faculty WHERE user_id = ?");
    $faculty_id->execute([$user_id]);
    $faculty_data = $faculty_id->fetch();
    
    if ($faculty_data) {
        $faculty_id = $faculty_data['id'];
        $queries = [
            'courses' => "SELECT COUNT(*) as count FROM courses WHERE faculty_id = $faculty_id AND status = 'Active'",
            'students' => "SELECT COUNT(DISTINCT e.student_id) as count FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.faculty_id = $faculty_id AND e.status = 'Enrolled'",
            'attendance_rate' => "SELECT ROUND(AVG(CASE WHEN a.status = 'Present' THEN 100 ELSE 0 END), 1) as rate FROM attendance a JOIN courses c ON a.course_id = c.id WHERE c.faculty_id = $faculty_id AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
        ];
    } else {
        // If faculty data not found, set default queries
        $queries = [
            'courses' => "SELECT 0 as count",
            'students' => "SELECT 0 as count", 
            'attendance_rate' => "SELECT 0 as rate"
        ];
    }
} else {
    // Student sees their own data
    $student_id = $db->prepare("SELECT id FROM students WHERE user_id = ?");
    $student_id->execute([$user_id]);
    $student_data = $student_id->fetch();
    
    if ($student_data) {
        $student_id = $student_data['id'];
        $queries = [
            'courses' => "SELECT COUNT(*) as count FROM enrollments WHERE student_id = $student_id AND status = 'Enrolled'",
            'attendance_rate' => "SELECT ROUND(AVG(CASE WHEN status = 'Present' THEN 100 ELSE 0 END), 1) as rate FROM attendance WHERE student_id = $student_id AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
        ];
    } else {
        // If student data not found, set default queries
        $queries = [
            'courses' => "SELECT 0 as count",
            'attendance_rate' => "SELECT 0 as rate"
        ];
    }
}

// Execute queries and get results
foreach ($queries as $key => $query) {
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    $stats[$key] = $result['count'] ?? $result['rate'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - College ERP</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                
                <?php if ($user_role === 'admin'): ?>
                <li><a href="students/index.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="faculty/index.php"><i class="fas fa-chalkboard-teacher"></i> Faculty</a></li>
                <li><a href="courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="departments/index.php"><i class="fas fa-building"></i> Departments</a></li>
                <li><a href="attendance/index.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="grades/index.php"><i class="fas fa-chart-line"></i> Grades</a></li>
                <li><a href="notices/index.php"><i class="fas fa-bullhorn"></i> Notices</a></li>
                <?php elseif ($user_role === 'faculty'): ?>
                <li><a href="faculty/courses.php"><i class="fas fa-book"></i> My Courses</a></li>
                <li><a href="faculty/students.php"><i class="fas fa-user-graduate"></i> My Students</a></li>
                <li><a href="faculty/attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="faculty/grades.php"><i class="fas fa-chart-line"></i> Grades</a></li>
                <?php else: ?>
                <li><a href="student/courses.php"><i class="fas fa-book"></i> My Courses</a></li>
                <li><a href="student/attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="student/grades.php"><i class="fas fa-chart-line"></i> Grades</a></li>
                <li><a href="student/notices.php"><i class="fas fa-bullhorn"></i> Notices</a></li>
                <?php endif; ?>
                
                <li><a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($username); ?> (<?php echo ucfirst($user_role); ?>)</span>
                    <a href="auth/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <?php if ($user_role === 'admin'): ?>
                <div class="stat-card">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-content">
                        <h4>Total Students</h4>
                        <p>Active students</p>
                    </div>
                    <div class="stat-number" id="total-students"><?php echo $stats['students'] ?? 0; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon faculty">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-content">
                        <h4>Total Faculty</h4>
                        <p>Active faculty members</p>
                    </div>
                    <div class="stat-number" id="total-faculty"><?php echo $stats['faculty'] ?? 0; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon courses">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h4>Total Courses</h4>
                        <p>Active courses</p>
                    </div>
                    <div class="stat-number" id="total-courses"><?php echo $stats['courses'] ?? 0; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon attendance">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h4>Attendance Rate</h4>
                        <p>Last 30 days</p>
                    </div>
                    <div class="stat-number" id="attendance-rate"><?php echo $stats['attendance_rate'] ?? 0; ?>%</div>
                </div>
                
                <?php elseif ($user_role === 'faculty'): ?>
                <div class="stat-card">
                    <div class="stat-icon courses">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h4>My Courses</h4>
                        <p>Courses I teach</p>
                    </div>
                    <div class="stat-number" id="total-courses"><?php echo $stats['courses'] ?? 0; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-content">
                        <h4>My Students</h4>
                        <p>Students in my courses</p>
                    </div>
                    <div class="stat-number" id="total-students"><?php echo $stats['students'] ?? 0; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon attendance">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h4>Attendance Rate</h4>
                        <p>My courses - Last 30 days</p>
                    </div>
                    <div class="stat-number" id="attendance-rate"><?php echo $stats['attendance_rate'] ?? 0; ?>%</div>
                </div>
                
                <?php else: ?>
                <div class="stat-card">
                    <div class="stat-icon courses">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h4>My Courses</h4>
                        <p>Enrolled courses</p>
                    </div>
                    <div class="stat-number" id="total-courses"><?php echo $stats['courses'] ?? 0; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon attendance">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h4>My Attendance</h4>
                        <p>Last 30 days</p>
                    </div>
                    <div class="stat-number" id="attendance-rate"><?php echo $stats['attendance_rate'] ?? 0; ?>%</div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Activities -->
            <div class="card">
                <div class="card-header">
                    <h3>Recent Activities</h3>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activity</th>
                                <th>Details</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Sample recent activities based on user role
                            if ($user_role === 'admin') {
                                $activities = [
                                    [
                                        'date' => '2025-09-19',
                                        'activity' => 'New Student Registration',
                                        'details' => 'John Smith registered for Computer Science program',
                                        'status' => 'success'
                                    ],
                                    [
                                        'date' => '2025-09-18',
                                        'activity' => 'Faculty Assignment',
                                        'details' => 'Dr. Johnson assigned to CS101 - Introduction to Programming',
                                        'status' => 'info'
                                    ],
                                    [
                                        'date' => '2025-09-17',
                                        'activity' => 'Course Creation',
                                        'details' => 'New course "Data Structures" added to Computer Science department',
                                        'status' => 'success'
                                    ],
                                    [
                                        'date' => '2025-09-16',
                                        'activity' => 'System Update',
                                        'details' => 'College ERP system updated to version 2.1',
                                        'status' => 'warning'
                                    ],
                                    [
                                        'date' => '2025-09-15',
                                        'activity' => 'Notice Published',
                                        'details' => 'Academic calendar for Fall 2025 published',
                                        'status' => 'info'
                                    ]
                                ];
                            } elseif ($user_role === 'faculty') {
                                $activities = [
                                    [
                                        'date' => '2025-09-19',
                                        'activity' => 'Attendance Marked',
                                        'details' => 'Marked attendance for CS101 - 42 present, 3 absent',
                                        'status' => 'success'
                                    ],
                                    [
                                        'date' => '2025-09-18',
                                        'activity' => 'Grade Submitted',
                                        'details' => 'Submitted grades for CS101 Mid-term Exam',
                                        'status' => 'success'
                                    ],
                                    [
                                        'date' => '2025-09-17',
                                        'activity' => 'Course Material Uploaded',
                                        'details' => 'Uploaded lecture notes for Data Structures',
                                        'status' => 'info'
                                    ],
                                    [
                                        'date' => '2025-09-16',
                                        'activity' => 'Student Query',
                                        'details' => 'Responded to student query about assignment deadline',
                                        'status' => 'warning'
                                    ],
                                    [
                                        'date' => '2025-09-15',
                                        'activity' => 'Course Schedule',
                                        'details' => 'Updated office hours for CS101 consultation',
                                        'status' => 'info'
                                    ]
                                ];
                            } else {
                                $activities = [
                                    [
                                        'date' => '2025-09-19',
                                        'activity' => 'Course Enrollment',
                                        'details' => 'Successfully enrolled in CS201 - Data Structures',
                                        'status' => 'success'
                                    ],
                                    [
                                        'date' => '2025-09-18',
                                        'activity' => 'Grade Received',
                                        'details' => 'Received grade A for CS101 Mid-term Exam',
                                        'status' => 'success'
                                    ],
                                    [
                                        'date' => '2025-09-17',
                                        'activity' => 'Assignment Submitted',
                                        'details' => 'Submitted Programming Assignment 1',
                                        'status' => 'info'
                                    ],
                                    [
                                        'date' => '2025-09-16',
                                        'activity' => 'Attendance Check',
                                        'details' => 'Checked attendance record - 95% attendance rate',
                                        'status' => 'success'
                                    ],
                                    [
                                        'date' => '2025-09-15',
                                        'activity' => 'Notice Read',
                                        'details' => 'Read important notice about exam schedule',
                                        'status' => 'info'
                                    ]
                                ];
                            }
                            
                            foreach ($activities as $activity) {
                                echo '<tr>';
                                echo '<td>' . date('M d, Y', strtotime($activity['date'])) . '</td>';
                                echo '<td><strong>' . htmlspecialchars($activity['activity']) . '</strong></td>';
                                echo '<td>' . htmlspecialchars($activity['details']) . '</td>';
                                echo '<td><span class="badge badge-' . $activity['status'] . '">' . ucfirst($activity['status']) . '</span></td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
