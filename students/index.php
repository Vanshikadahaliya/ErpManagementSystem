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
                addStudent($db);
                break;
            case 'edit':
                editStudent($db);
                break;
            case 'delete':
                deleteStudent($db);
                break;
        }
    }
}

// Get all students with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE s.first_name LIKE :search OR s.last_name LIKE :search OR s.student_id LIKE :search";
    $params[':search'] = "%$search%";
}

$count_query = "SELECT COUNT(*) as total FROM students s $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_records = $count_stmt->fetch()['total'];
$total_pages = ceil($total_records / $limit);

$query = "SELECT s.*, u.email FROM students s 
          LEFT JOIN users u ON s.user_id = u.id 
          $where_clause 
          ORDER BY s.created_at DESC 
          LIMIT $limit OFFSET $offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$students = $stmt->fetchAll();

function addStudent($db) {
    $student_id = sanitizeInput($_POST['student_id']);
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = sanitizeInput($_POST['gender']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $enrollment_date = $_POST['enrollment_date'];
    
    // Check if student ID already exists
    $check_query = "SELECT id FROM students WHERE student_id = :student_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':student_id', $student_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = 'Student ID already exists';
        return;
    }
    
    $insert_query = "INSERT INTO students (student_id, first_name, last_name, date_of_birth, gender, phone, address, enrollment_date) 
                     VALUES (:student_id, :first_name, :last_name, :date_of_birth, :gender, :phone, :address, :enrollment_date)";
    
    $stmt = $db->prepare($insert_query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':date_of_birth', $date_of_birth);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':enrollment_date', $enrollment_date);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Student added successfully';
    } else {
        $_SESSION['error'] = 'Failed to add student';
    }
}

function editStudent($db) {
    $id = (int)$_POST['id'];
    $student_id = sanitizeInput($_POST['student_id']);
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = sanitizeInput($_POST['gender']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $enrollment_date = $_POST['enrollment_date'];
    $status = sanitizeInput($_POST['status']);
    
    $update_query = "UPDATE students SET 
                     student_id = :student_id, 
                     first_name = :first_name, 
                     last_name = :last_name, 
                     date_of_birth = :date_of_birth, 
                     gender = :gender, 
                     phone = :phone, 
                     address = :address, 
                     enrollment_date = :enrollment_date,
                     status = :status
                     WHERE id = :id";
    
    $stmt = $db->prepare($update_query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':date_of_birth', $date_of_birth);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':enrollment_date', $enrollment_date);
    $stmt->bindParam(':status', $status);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Student updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update student';
    }
}

function deleteStudent($db) {
    $id = (int)$_POST['id'];
    
    $delete_query = "DELETE FROM students WHERE id = :id";
    $stmt = $db->prepare($delete_query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Student deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete student';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - College ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-graduation-cap"></i> College ERP</h2>
                <p>Student Management</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="index.php" class="active"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="../faculty/index.php"><i class="fas fa-chalkboard-teacher"></i> Faculty</a></li>
                <li><a href="../courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
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
                <h1>Student Management</h1>
                <button class="btn btn-primary" onclick="openModal('addStudentModal')">
                    <i class="fas fa-plus"></i> Add Student
                </button>
            </div>
            
            <!-- Search and Filter -->
            <div class="card">
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Search students..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Students Table -->
            <div class="card">
                <div class="card-header">
                    <h3>Students List</h3>
                    <span>Total: <?php echo $total_records; ?> students</span>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Enrollment Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No students found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($student['enrollment_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($student['status']); ?>">
                                        <?php echo $student['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteStudent(<?php echo $student['id']; ?>)">
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
    
    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Student</h3>
                <span class="close" onclick="closeModal('addStudentModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" id="student_id" name="student_id" required>
                    </div>
                    <div class="form-group">
                        <label for="enrollment_date">Enrollment Date</label>
                        <input type="date" id="enrollment_date" name="enrollment_date" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" required>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addStudentModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Student Modal -->
    <div id="editStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Student</h3>
                <span class="close" onclick="closeModal('editStudentModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_student_id">Student ID</label>
                        <input type="text" id="edit_student_id" name="student_id" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_enrollment_date">Enrollment Date</label>
                        <input type="date" id="edit_enrollment_date" name="enrollment_date" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_first_name">First Name</label>
                        <input type="text" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_last_name">Last Name</label>
                        <input type="text" id="edit_last_name" name="last_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_date_of_birth">Date of Birth</label>
                        <input type="date" id="edit_date_of_birth" name="date_of_birth" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_gender">Gender</label>
                        <select id="edit_gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_phone">Phone</label>
                    <input type="tel" id="edit_phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="edit_address">Address</label>
                    <textarea id="edit_address" name="address" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Graduated">Graduated</option>
                        <option value="Suspended">Suspended</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editStudentModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Student</button>
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
                <p>Are you sure you want to delete this student? This action cannot be undone.</p>
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
        
        function editStudent(student) {
            document.getElementById('edit_id').value = student.id;
            document.getElementById('edit_student_id').value = student.student_id;
            document.getElementById('edit_first_name').value = student.first_name;
            document.getElementById('edit_last_name').value = student.last_name;
            document.getElementById('edit_date_of_birth').value = student.date_of_birth;
            document.getElementById('edit_gender').value = student.gender;
            document.getElementById('edit_phone').value = student.phone;
            document.getElementById('edit_address').value = student.address;
            document.getElementById('edit_enrollment_date').value = student.enrollment_date;
            document.getElementById('edit_status').value = student.status;
            openModal('editStudentModal');
        }
        
        function deleteStudent(id) {
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
        .status-graduated { background: #d1ecf1; color: #0c5460; }
        .status-suspended { background: #fff3cd; color: #856404; }
        
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
