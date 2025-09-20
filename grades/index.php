<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('../index.php');
}

$database = new Database();
$db = $database->getConnection();
$user_role = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_grade':
                addGrade($db);
                break;
            case 'update_grade':
                updateGrade($db);
                break;
        }
    }
}

// Get courses based on user role
if ($user_role === 'admin') {
    $courses_query = "SELECT c.*, f.first_name, f.last_name FROM courses c 
                      LEFT JOIN faculty f ON c.faculty_id = f.id 
                      WHERE c.status = 'Active' ORDER BY c.course_name";
} elseif ($user_role === 'faculty') {
    $faculty_id = $db->prepare("SELECT id FROM faculty WHERE user_id = ?");
    $faculty_id->execute([$_SESSION['user_id']]);
    $faculty_data = $faculty_id->fetch();
    
    if ($faculty_data) {
        $courses_query = "SELECT c.*, f.first_name, f.last_name FROM courses c 
                          LEFT JOIN faculty f ON c.faculty_id = f.id 
                          WHERE c.faculty_id = {$faculty_data['id']} AND c.status = 'Active' 
                          ORDER BY c.course_name";
    } else {
        $courses_query = "SELECT * FROM courses WHERE 1=0";
    }
} else {
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
        $courses_query = "SELECT * FROM courses WHERE 1=0";
    }
}

$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll();

$selected_course = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Get grades for selected course
$grades = [];
if ($selected_course > 0) {
    if ($user_role === 'student') {
        $student_id = $db->prepare("SELECT id FROM students WHERE user_id = ?");
        $student_id->execute([$_SESSION['user_id']]);
        $student_data = $student_id->fetch();
        
        if ($student_data) {
            $grades_query = "SELECT g.*, s.first_name, s.last_name, s.student_id 
                             FROM grades g 
                             JOIN students s ON g.student_id = s.id 
                             WHERE g.course_id = :course_id AND g.student_id = :student_id 
                             ORDER BY g.created_at DESC";
            $grades_stmt = $db->prepare($grades_query);
            $grades_stmt->bindParam(':course_id', $selected_course);
            $grades_stmt->bindParam(':student_id', $student_data['id']);
            $grades_stmt->execute();
            $grades = $grades_stmt->fetchAll();
        }
    } else {
        $grades_query = "SELECT g.*, s.first_name, s.last_name, s.student_id 
                         FROM grades g 
                         JOIN students s ON g.student_id = s.id 
                         WHERE g.course_id = :course_id 
                         ORDER BY s.first_name, s.last_name, g.created_at DESC";
        $grades_stmt = $db->prepare($grades_query);
        $grades_stmt->bindParam(':course_id', $selected_course);
        $grades_stmt->execute();
        $grades = $grades_stmt->fetchAll();
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

function addGrade($db) {
    $student_id = (int)$_POST['student_id'];
    $course_id = (int)$_POST['course_id'];
    $assignment_type = sanitizeInput($_POST['assignment_type']);
    $marks_obtained = (float)$_POST['marks_obtained'];
    $total_marks = (float)$_POST['total_marks'];
    $grade = calculateGrade($marks_obtained, $total_marks);
    $remarks = sanitizeInput($_POST['remarks']);
    
    $insert_query = "INSERT INTO grades (student_id, course_id, assignment_type, marks_obtained, total_marks, grade, remarks) 
                     VALUES (:student_id, :course_id, :assignment_type, :marks_obtained, :total_marks, :grade, :remarks)";
    
    $stmt = $db->prepare($insert_query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':assignment_type', $assignment_type);
    $stmt->bindParam(':marks_obtained', $marks_obtained);
    $stmt->bindParam(':total_marks', $total_marks);
    $stmt->bindParam(':grade', $grade);
    $stmt->bindParam(':remarks', $remarks);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Grade added successfully';
    } else {
        $_SESSION['error'] = 'Failed to add grade';
    }
}

function updateGrade($db) {
    $id = (int)$_POST['id'];
    $marks_obtained = (float)$_POST['marks_obtained'];
    $total_marks = (float)$_POST['total_marks'];
    $grade = calculateGrade($marks_obtained, $total_marks);
    $remarks = sanitizeInput($_POST['remarks']);
    
    $update_query = "UPDATE grades SET marks_obtained = :marks_obtained, total_marks = :total_marks, grade = :grade, remarks = :remarks WHERE id = :id";
    
    $stmt = $db->prepare($update_query);
    $stmt->bindParam(':marks_obtained', $marks_obtained);
    $stmt->bindParam(':total_marks', $total_marks);
    $stmt->bindParam(':grade', $grade);
    $stmt->bindParam(':remarks', $remarks);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Grade updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update grade';
    }
}

function calculateGrade($marks_obtained, $total_marks) {
    if ($total_marks == 0) return 'N/A';
    
    $percentage = ($marks_obtained / $total_marks) * 100;
    
    if ($percentage >= 90) return 'A+';
    elseif ($percentage >= 80) return 'A';
    elseif ($percentage >= 70) return 'B+';
    elseif ($percentage >= 60) return 'B';
    elseif ($percentage >= 50) return 'C+';
    elseif ($percentage >= 40) return 'C';
    else return 'F';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Management - College ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-graduation-cap"></i> College ERP</h2>
                <p>Grade Management</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <?php if ($user_role === 'admin'): ?>
                <li><a href="../students/index.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="../faculty/index.php"><i class="fas fa-chalkboard-teacher"></i> Faculty</a></li>
                <li><a href="../courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
                <?php endif; ?>
                <li><a href="../attendance/index.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="index.php" class="active"><i class="fas fa-chart-line"></i> Grades</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Grade Management</h1>
                <?php if ($user_role !== 'student'): ?>
                <button class="btn btn-primary" onclick="openModal('addGradeModal')">
                    <i class="fas fa-plus"></i> Add Grade
                </button>
                <?php endif; ?>
            </div>
            
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
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i> View Grades
                        </button>
                    </div>
                </form>
            </div>
            
            <?php if ($selected_course > 0): ?>
            <div class="card">
                <div class="card-header">
                    <h3>Grades</h3>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Assignment Type</th>
                                <th>Marks Obtained</th>
                                <th>Total Marks</th>
                                <th>Grade</th>
                                <th>Remarks</th>
                                <?php if ($user_role !== 'student'): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($grades)): ?>
                            <tr>
                                <td colspan="<?php echo $user_role === 'student' ? '7' : '8'; ?>" class="text-center">No grades found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($grades as $grade): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']); ?></td>
                                <td><?php echo $grade['assignment_type']; ?></td>
                                <td><?php echo $grade['marks_obtained']; ?></td>
                                <td><?php echo $grade['total_marks']; ?></td>
                                <td>
                                    <span class="grade-badge grade-<?php echo strtolower($grade['grade']); ?>">
                                        <?php echo $grade['grade']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($grade['remarks'] ?? ''); ?></td>
                                <?php if ($user_role !== 'student'): ?>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="editGrade(<?php echo htmlspecialchars(json_encode($grade)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add Grade Modal -->
    <?php if ($user_role !== 'student'): ?>
    <div id="addGradeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Grade</h3>
                <span class="close" onclick="closeModal('addGradeModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_grade">
                <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
                
                <div class="form-group">
                    <label for="student_id">Student</label>
                    <select id="student_id" name="student_id" required>
                        <option value="">Select Student</option>
                        <?php foreach ($enrolled_students as $student): ?>
                        <option value="<?php echo $student['id']; ?>">
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['student_id'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="assignment_type">Assignment Type</label>
                        <select id="assignment_type" name="assignment_type" required>
                            <option value="">Select Type</option>
                            <option value="Quiz">Quiz</option>
                            <option value="Midterm">Midterm</option>
                            <option value="Final">Final</option>
                            <option value="Assignment">Assignment</option>
                            <option value="Project">Project</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="marks_obtained">Marks Obtained</label>
                        <input type="number" id="marks_obtained" name="marks_obtained" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="total_marks">Total Marks</label>
                        <input type="number" id="total_marks" name="total_marks" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" rows="3"></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addGradeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Grade</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="../assets/js/main.js"></script>
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function editGrade(grade) {
            // Implementation for editing grades
            alert('Edit functionality can be added here');
        }
        
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
    
    <style>
        .grade-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .grade-a+ { background: #d4edda; color: #155724; }
        .grade-a { background: #d1ecf1; color: #0c5460; }
        .grade-b+ { background: #fff3cd; color: #856404; }
        .grade-b { background: #f8d7da; color: #721c24; }
        .grade-c+ { background: #e2e3e5; color: #383d41; }
        .grade-c { background: #f5c6cb; color: #721c24; }
        .grade-f { background: #f8d7da; color: #721c24; }
    </style>
</body>
</html>
