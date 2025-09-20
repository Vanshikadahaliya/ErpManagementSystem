<?php
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../index.php');
}

$database = new Database();
$db = $database->getConnection();
$user_role = $_SESSION['role'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_attendance':
                markAttendance($db);
                break;
            case 'update_attendance':
                updateAttendance($db);
                break;
        }
    }
}

// Get courses based on user role
if ($user_role === 'admin') {
    // Admin can see all courses
    $courses_query = "SELECT c.*, f.first_name, f.last_name FROM courses c 
                      LEFT JOIN faculty f ON c.faculty_id = f.id 
                      WHERE c.status = 'Active' 
                      ORDER BY c.course_name";
} elseif ($user_role === 'faculty') {
    // Faculty can see only their courses
    $faculty_id = $db->prepare("SELECT id FROM faculty WHERE user_id = ?");
    $faculty_id->execute([$_SESSION['user_id']]);
    $faculty_data = $faculty_id->fetch();
    
    if ($faculty_data) {
        $courses_query = "SELECT c.*, f.first_name, f.last_name FROM courses c 
                          LEFT JOIN faculty f ON c.faculty_id = f.id 
                          WHERE c.faculty_id = {$faculty_data['id']} AND c.status = 'Active' 
                          ORDER BY c.course_name";
    } else {
        $courses_query = "SELECT * FROM courses WHERE 1=0"; // No courses
    }
} else {
    // Student can see only enrolled courses
    $student_id = $db->prepare("SELECT id FROM students WHERE user_id = ?");
    $student_id->execute([$_SESSION['user_id']]);
    $student_data = $student_id->fetch();
    
    if ($student_data) {
        $courses_query = "SELECT c.*, f.first_name, f.last_name FROM courses c 
                          LEFT JOIN faculty f ON c.faculty_id = f.id 
                          JOIN enrollments e ON c.id = e.course_id 
                          WHERE e.student_id = {$student_data['id']} AND e.status = 'Enrolled' AND c.status = 'Active' 
                          ORDER BY c.course_name";
    } else {
        $courses_query = "SELECT * FROM courses WHERE 1=0"; // No courses
    }
}

$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll();

// Get selected course and date
$selected_course = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get attendance records for selected course and date
$attendance_records = [];
if ($selected_course > 0) {
    if ($user_role === 'student') {
        // Student sees only their attendance
        $student_id = $db->prepare("SELECT id FROM students WHERE user_id = ?");
        $student_id->execute([$_SESSION['user_id']]);
        $student_data = $student_id->fetch();
        
        if ($student_data) {
            $att_query = "SELECT a.*, s.first_name, s.last_name, s.student_id 
                          FROM attendance a 
                          JOIN students s ON a.student_id = s.id 
                          WHERE a.course_id = :course_id AND a.date = :date AND a.student_id = :student_id";
            $att_stmt = $db->prepare($att_query);
            $att_stmt->bindParam(':course_id', $selected_course);
            $att_stmt->bindParam(':date', $selected_date);
            $att_stmt->bindParam(':student_id', $student_data['id']);
            $att_stmt->execute();
            $attendance_records = $att_stmt->fetchAll();
        }
    } else {
        // Faculty/Admin sees all students in the course
        $att_query = "SELECT a.*, s.first_name, s.last_name, s.student_id 
                      FROM attendance a 
                      JOIN students s ON a.student_id = s.id 
                      WHERE a.course_id = :course_id AND a.date = :date 
                      ORDER BY s.first_name, s.last_name";
        $att_stmt = $db->prepare($att_query);
        $att_stmt->bindParam(':course_id', $selected_course);
        $att_stmt->bindParam(':date', $selected_date);
        $att_stmt->execute();
        $attendance_records = $att_stmt->fetchAll();
    }
}

// Get enrolled students for the selected course (for faculty/admin)
$enrolled_students = [];
if ($selected_course > 0 && ($user_role === 'admin' || $user_role === 'faculty')) {
    $enrolled_query = "SELECT s.* FROM students s 
                       JOIN enrollments e ON s.id = e.student_id 
                       WHERE e.course_id = :course_id AND e.status = 'Enrolled' AND s.status = 'Active'
                       ORDER BY s.first_name, s.last_name";
    $enrolled_stmt = $db->prepare($enrolled_query);
    $enrolled_stmt->bindParam(':course_id', $selected_course);
    $enrolled_stmt->execute();
    $enrolled_students = $enrolled_stmt->fetchAll();
}

function markAttendance($db) {
    $course_id = (int)$_POST['course_id'];
    $date = $_POST['date'];
    $attendance_data = $_POST['attendance'];
    
    foreach ($attendance_data as $student_id => $status) {
        // Check if attendance already exists
        $check_query = "SELECT id FROM attendance WHERE student_id = :student_id AND course_id = :course_id AND date = :date";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':student_id', $student_id);
        $check_stmt->bindParam(':course_id', $course_id);
        $check_stmt->bindParam(':date', $date);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            // Update existing record
            $update_query = "UPDATE attendance SET status = :status WHERE student_id = :student_id AND course_id = :course_id AND date = :date";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':status', $status);
            $update_stmt->bindParam(':student_id', $student_id);
            $update_stmt->bindParam(':course_id', $course_id);
            $update_stmt->bindParam(':date', $date);
            $update_stmt->execute();
        } else {
            // Insert new record
            $insert_query = "INSERT INTO attendance (student_id, course_id, date, status) VALUES (:student_id, :course_id, :date, :status)";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bindParam(':student_id', $student_id);
            $insert_stmt->bindParam(':course_id', $course_id);
            $insert_stmt->bindParam(':date', $date);
            $insert_stmt->bindParam(':status', $status);
            $insert_stmt->execute();
        }
    }
    
    $_SESSION['success'] = 'Attendance marked successfully';
}

function updateAttendance($db) {
    $attendance_id = (int)$_POST['attendance_id'];
    $status = sanitizeInput($_POST['status']);
    $remarks = sanitizeInput($_POST['remarks']);
    
    $update_query = "UPDATE attendance SET status = :status, remarks = :remarks WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':status', $status);
    $update_stmt->bindParam(':remarks', $remarks);
    $update_stmt->bindParam(':id', $attendance_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = 'Attendance updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update attendance';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - College ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-graduation-cap"></i> College ERP</h2>
                <p>Attendance Management</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <?php if ($user_role === 'admin'): ?>
                <li><a href="../students/index.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="../faculty/index.php"><i class="fas fa-chalkboard-teacher"></i> Faculty</a></li>
                <li><a href="../courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="../departments/index.php"><i class="fas fa-building"></i> Departments</a></li>
                <?php endif; ?>
                <li><a href="index.php" class="active"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="../grades/index.php"><i class="fas fa-chart-line"></i> Grades</a></li>
                <?php if ($user_role === 'admin'): ?>
                <li><a href="../notices/index.php"><i class="fas fa-bullhorn"></i> Notices</a></li>
                <?php endif; ?>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Attendance Management</h1>
            </div>
            
            <!-- Course and Date Selection -->
            <div class="card">
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <label for="course_id">Select Course</label>
                        <select id="course_id" name="course_id" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>" <?php echo $selected_course == $course['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" value="<?php echo $selected_date; ?>" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i> View Attendance
                        </button>
                    </div>
                </form>
            </div>
            
            <?php if ($selected_course > 0): ?>
            <!-- Attendance Records -->
            <div class="card">
                <div class="card-header">
                    <h3>Attendance Records</h3>
                    <span>Date: <?php echo date('M d, Y', strtotime($selected_date)); ?></span>
                </div>
                
                <?php if ($user_role === 'student'): ?>
                <!-- Student View -->
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendance_records)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No attendance record found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($attendance_records as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($record['status']); ?>">
                                        <?php echo $record['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($record['remarks'] ?? ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php else: ?>
                <!-- Faculty/Admin View -->
                <form method="POST" action="">
                    <input type="hidden" name="action" value="mark_attendance">
                    <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
                    <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
                    
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($enrolled_students)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No students enrolled in this course</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($enrolled_students as $student): ?>
                                <?php
                                // Find existing attendance record
                                $existing_record = null;
                                foreach ($attendance_records as $record) {
                                    if ($record['student_id'] == $student['id']) {
                                        $existing_record = $record;
                                        break;
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                    <td>
                                        <select name="attendance[<?php echo $student['id']; ?>]" class="form-control">
                                            <option value="Present" <?php echo ($existing_record && $existing_record['status'] == 'Present') ? 'selected' : ''; ?>>Present</option>
                                            <option value="Absent" <?php echo ($existing_record && $existing_record['status'] == 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                            <option value="Late" <?php echo ($existing_record && $existing_record['status'] == 'Late') ? 'selected' : ''; ?>>Late</option>
                                            <option value="Excused" <?php echo ($existing_record && $existing_record['status'] == 'Excused') ? 'selected' : ''; ?>>Excused</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="remarks[<?php echo $student['id']; ?>]" 
                                               value="<?php echo htmlspecialchars($existing_record['remarks'] ?? ''); ?>" 
                                               class="form-control" placeholder="Remarks">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-20">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Attendance
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-present { background: #d4edda; color: #155724; }
        .status-absent { background: #f8d7da; color: #721c24; }
        .status-late { background: #fff3cd; color: #856404; }
        .status-excused { background: #d1ecf1; color: #0c5460; }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }
    </style>
</body>
</html>
