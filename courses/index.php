<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                addCourse($db);
                break;
            case 'edit':
                editCourse($db);
                break;
            case 'delete':
                deleteCourse($db);
                break;
        }
    }
}

// Get all courses with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE c.course_name LIKE :search OR c.course_code LIKE :search";
    $params[':search'] = "%$search%";
}

$count_query = "SELECT COUNT(*) as total FROM courses c $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_records = $count_stmt->fetch()['total'];
$total_pages = ceil($total_records / $limit);

$query = "SELECT c.*, f.first_name, f.last_name, d.name as department_name 
          FROM courses c 
          LEFT JOIN faculty f ON c.faculty_id = f.id 
          LEFT JOIN departments d ON c.department_id = d.id
          $where_clause 
          ORDER BY c.created_at DESC 
          LIMIT $limit OFFSET $offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$courses = $stmt->fetchAll();

// Get faculty and departments for dropdowns
$faculty_query = "SELECT id, first_name, last_name FROM faculty WHERE status = 'Active' ORDER BY first_name";
$faculty_stmt = $db->prepare($faculty_query);
$faculty_stmt->execute();
$faculty_list = $faculty_stmt->fetchAll();

$dept_query = "SELECT id, name FROM departments ORDER BY name";
$dept_stmt = $db->prepare($dept_query);
$dept_stmt->execute();
$departments = $dept_stmt->fetchAll();

function addCourse($db) {
    $course_code = sanitizeInput($_POST['course_code']);
    $course_name = sanitizeInput($_POST['course_name']);
    $description = sanitizeInput($_POST['description']);
    $credits = (int)$_POST['credits'];
    $department_id = (int)$_POST['department_id'];
    $faculty_id = (int)$_POST['faculty_id'];
    $semester = sanitizeInput($_POST['semester']);
    $academic_year = sanitizeInput($_POST['academic_year']);
    
    // Check if course code already exists
    $check_query = "SELECT id FROM courses WHERE course_code = :course_code";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':course_code', $course_code);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = 'Course code already exists';
        return;
    }
    
    $insert_query = "INSERT INTO courses (course_code, course_name, description, credits, department_id, faculty_id, semester, academic_year) 
                     VALUES (:course_code, :course_name, :description, :credits, :department_id, :faculty_id, :semester, :academic_year)";
    
    $stmt = $db->prepare($insert_query);
    $stmt->bindParam(':course_code', $course_code);
    $stmt->bindParam(':course_name', $course_name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':credits', $credits);
    $stmt->bindParam(':department_id', $department_id);
    $stmt->bindParam(':faculty_id', $faculty_id);
    $stmt->bindParam(':semester', $semester);
    $stmt->bindParam(':academic_year', $academic_year);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Course added successfully';
    } else {
        $_SESSION['error'] = 'Failed to add course';
    }
}

function editCourse($db) {
    $id = (int)$_POST['id'];
    $course_code = sanitizeInput($_POST['course_code']);
    $course_name = sanitizeInput($_POST['course_name']);
    $description = sanitizeInput($_POST['description']);
    $credits = (int)$_POST['credits'];
    $department_id = (int)$_POST['department_id'];
    $faculty_id = (int)$_POST['faculty_id'];
    $semester = sanitizeInput($_POST['semester']);
    $academic_year = sanitizeInput($_POST['academic_year']);
    $status = sanitizeInput($_POST['status']);
    
    $update_query = "UPDATE courses SET 
                     course_code = :course_code, 
                     course_name = :course_name, 
                     description = :description, 
                     credits = :credits, 
                     department_id = :department_id,
                     faculty_id = :faculty_id,
                     semester = :semester,
                     academic_year = :academic_year,
                     status = :status
                     WHERE id = :id";
    
    $stmt = $db->prepare($update_query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':course_code', $course_code);
    $stmt->bindParam(':course_name', $course_name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':credits', $credits);
    $stmt->bindParam(':department_id', $department_id);
    $stmt->bindParam(':faculty_id', $faculty_id);
    $stmt->bindParam(':semester', $semester);
    $stmt->bindParam(':academic_year', $academic_year);
    $stmt->bindParam(':status', $status);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Course updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update course';
    }
}

function deleteCourse($db) {
    $id = (int)$_POST['id'];
    
    $delete_query = "DELETE FROM courses WHERE id = :id";
    $stmt = $db->prepare($delete_query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Course deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete course';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - College ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-graduation-cap"></i> College ERP</h2>
                <p>Course Management</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="../students/index.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="../faculty/index.php"><i class="fas fa-chalkboard-teacher"></i> Faculty</a></li>
                <li><a href="index.php" class="active"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="../departments/index.php"><i class="fas fa-building"></i> Departments</a></li>
                <li><a href="../attendance/index.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="../grades/index.php"><i class="fas fa-chart-line"></i> Grades</a></li>
                <li><a href="../notices/index.php"><i class="fas fa-bullhorn"></i> Notices</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Course Management</h1>
                <button class="btn btn-primary" onclick="openModal('addCourseModal')">
                    <i class="fas fa-plus"></i> Add Course
                </button>
            </div>
            
            <!-- Search and Filter -->
            <div class="card">
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Search courses..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Courses Table -->
            <div class="card">
                <div class="card-header">
                    <h3>Courses List</h3>
                    <span>Total: <?php echo $total_records; ?> courses</span>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Department</th>
                                <th>Faculty</th>
                                <th>Credits</th>
                                <th>Semester</th>
                                <th>Academic Year</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No courses found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($course['department_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(($course['first_name'] ?? '') . ' ' . ($course['last_name'] ?? '')); ?></td>
                                <td><?php echo $course['credits']; ?></td>
                                <td><?php echo $course['semester']; ?></td>
                                <td><?php echo htmlspecialchars($course['academic_year']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($course['status']); ?>">
                                        <?php echo $course['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="editCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteCourse(<?php echo $course['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                       class="<?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Course Modal -->
    <div id="addCourseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Course</h3>
                <span class="close" onclick="closeModal('addCourseModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label for="course_code">Course Code</label>
                        <input type="text" id="course_code" name="course_code" required>
                    </div>
                    <div class="form-group">
                        <label for="credits">Credits</label>
                        <input type="number" id="credits" name="credits" min="1" max="6" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="course_name">Course Name</label>
                    <input type="text" id="course_name" name="course_name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="faculty_id">Faculty</label>
                        <select id="faculty_id" name="faculty_id" required>
                            <option value="">Select Faculty</option>
                            <?php foreach ($faculty_list as $faculty): ?>
                            <option value="<?php echo $faculty['id']; ?>"><?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="1">1st Semester</option>
                            <option value="2">2nd Semester</option>
                            <option value="3">3rd Semester</option>
                            <option value="4">4th Semester</option>
                            <option value="5">5th Semester</option>
                            <option value="6">6th Semester</option>
                            <option value="7">7th Semester</option>
                            <option value="8">8th Semester</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="academic_year">Academic Year</label>
                        <input type="text" id="academic_year" name="academic_year" placeholder="e.g., 2024-25" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addCourseModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Course</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Course Modal -->
    <div id="editCourseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Course</h3>
                <span class="close" onclick="closeModal('editCourseModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_course_code">Course Code</label>
                        <input type="text" id="edit_course_code" name="course_code" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_credits">Credits</label>
                        <input type="number" id="edit_credits" name="credits" min="1" max="6" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_course_name">Course Name</label>
                    <input type="text" id="edit_course_name" name="course_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_department_id">Department</label>
                        <select id="edit_department_id" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_faculty_id">Faculty</label>
                        <select id="edit_faculty_id" name="faculty_id" required>
                            <option value="">Select Faculty</option>
                            <?php foreach ($faculty_list as $faculty): ?>
                            <option value="<?php echo $faculty['id']; ?>"><?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_semester">Semester</label>
                        <select id="edit_semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="1">1st Semester</option>
                            <option value="2">2nd Semester</option>
                            <option value="3">3rd Semester</option>
                            <option value="4">4th Semester</option>
                            <option value="5">5th Semester</option>
                            <option value="6">6th Semester</option>
                            <option value="7">7th Semester</option>
                            <option value="8">8th Semester</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_academic_year">Academic Year</label>
                        <input type="text" id="edit_academic_year" name="academic_year" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editCourseModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Course</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <span class="close" onclick="closeModal('deleteModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <p>Are you sure you want to delete this course? This action cannot be undone.</p>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function editCourse(course) {
            document.getElementById('edit_id').value = course.id;
            document.getElementById('edit_course_code').value = course.course_code;
            document.getElementById('edit_course_name').value = course.course_name;
            document.getElementById('edit_description').value = course.description || '';
            document.getElementById('edit_credits').value = course.credits;
            document.getElementById('edit_department_id').value = course.department_id;
            document.getElementById('edit_faculty_id').value = course.faculty_id;
            document.getElementById('edit_semester').value = course.semester;
            document.getElementById('edit_academic_year').value = course.academic_year;
            document.getElementById('edit_status').value = course.status;
            openModal('editCourseModal');
        }
        
        function deleteCourse(id) {
            document.getElementById('delete_id').value = id;
            openModal('deleteModal');
        }
        
        // Close modal when clicking outside
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
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e1e5e9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
        }
        
        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }
        
        .close:hover {
            color: #333;
        }
        
        .modal form {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e1e5e9;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
            margin: 0 2px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }
        
        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
        }
        
        .pagination a:hover,
        .pagination a.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
    </style>
</body>
</html>
