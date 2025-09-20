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
                addFaculty($db);
                break;
            case 'edit':
                editFaculty($db);
                break;
            case 'delete':
                deleteFaculty($db);
                break;
        }
    }
}

// Get all faculty with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE f.first_name LIKE :search OR f.last_name LIKE :search OR f.faculty_id LIKE :search";
    $params[':search'] = "%$search%";
}

$count_query = "SELECT COUNT(*) as total FROM faculty f $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_records = $count_stmt->fetch()['total'];
$total_pages = ceil($total_records / $limit);

$query = "SELECT f.*, u.email FROM faculty f 
          LEFT JOIN users u ON f.user_id = u.id 
          $where_clause 
          ORDER BY f.created_at DESC 
          LIMIT $limit OFFSET $offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$faculty = $stmt->fetchAll();

// Get departments for dropdown
$dept_query = "SELECT id, name FROM departments ORDER BY name";
$dept_stmt = $db->prepare($dept_query);
$dept_stmt->execute();
$departments = $dept_stmt->fetchAll();

function addFaculty($db) {
    $faculty_id = sanitizeInput($_POST['faculty_id']);
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = sanitizeInput($_POST['gender']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $department = sanitizeInput($_POST['department']);
    $designation = sanitizeInput($_POST['designation']);
    $hire_date = $_POST['hire_date'];
    $salary = $_POST['salary'];
    
    // Check if faculty ID already exists
    $check_query = "SELECT id FROM faculty WHERE faculty_id = :faculty_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':faculty_id', $faculty_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = 'Faculty ID already exists';
        return;
    }
    
    $insert_query = "INSERT INTO faculty (faculty_id, first_name, last_name, date_of_birth, gender, phone, address, department, designation, hire_date, salary) 
                     VALUES (:faculty_id, :first_name, :last_name, :date_of_birth, :gender, :phone, :address, :department, :designation, :hire_date, :salary)";
    
    $stmt = $db->prepare($insert_query);
    $stmt->bindParam(':faculty_id', $faculty_id);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':date_of_birth', $date_of_birth);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':designation', $designation);
    $stmt->bindParam(':hire_date', $hire_date);
    $stmt->bindParam(':salary', $salary);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Faculty added successfully';
    } else {
        $_SESSION['error'] = 'Failed to add faculty';
    }
}

function editFaculty($db) {
    $id = (int)$_POST['id'];
    $faculty_id = sanitizeInput($_POST['faculty_id']);
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = sanitizeInput($_POST['gender']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $department = sanitizeInput($_POST['department']);
    $designation = sanitizeInput($_POST['designation']);
    $hire_date = $_POST['hire_date'];
    $salary = $_POST['salary'];
    $status = sanitizeInput($_POST['status']);
    
    $update_query = "UPDATE faculty SET 
                     faculty_id = :faculty_id, 
                     first_name = :first_name, 
                     last_name = :last_name, 
                     date_of_birth = :date_of_birth, 
                     gender = :gender, 
                     phone = :phone, 
                     address = :address, 
                     department = :department,
                     designation = :designation,
                     hire_date = :hire_date,
                     salary = :salary,
                     status = :status
                     WHERE id = :id";
    
    $stmt = $db->prepare($update_query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':faculty_id', $faculty_id);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':date_of_birth', $date_of_birth);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':designation', $designation);
    $stmt->bindParam(':hire_date', $hire_date);
    $stmt->bindParam(':salary', $salary);
    $stmt->bindParam(':status', $status);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Faculty updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update faculty';
    }
}

function deleteFaculty($db) {
    $id = (int)$_POST['id'];
    
    $delete_query = "DELETE FROM faculty WHERE id = :id";
    $stmt = $db->prepare($delete_query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Faculty deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete faculty';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Management - College ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-graduation-cap"></i> College ERP</h2>
                <p>Faculty Management</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="../students/index.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="index.php" class="active"><i class="fas fa-chalkboard-teacher"></i> Faculty</a></li>
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
                <h1>Faculty Management</h1>
                <button class="btn btn-primary" onclick="openModal('addFacultyModal')">
                    <i class="fas fa-plus"></i> Add Faculty
                </button>
            </div>
            
            <!-- Search and Filter -->
            <div class="card">
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Search faculty..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Faculty Table -->
            <div class="card">
                <div class="card-header">
                    <h3>Faculty List</h3>
                    <span>Total: <?php echo $total_records; ?> faculty members</span>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Faculty ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($faculty)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No faculty found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($faculty as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['faculty_id']); ?></td>
                                <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($member['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($member['department']); ?></td>
                                <td><?php echo htmlspecialchars($member['designation']); ?></td>
                                <td><?php echo htmlspecialchars($member['phone']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($member['status']); ?>">
                                        <?php echo $member['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="editFaculty(<?php echo htmlspecialchars(json_encode($member)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteFaculty(<?php echo $member['id']; ?>)">
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
    
    <!-- Add Faculty Modal -->
    <div id="addFacultyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Faculty</h3>
                <span class="close" onclick="closeModal('addFacultyModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label for="faculty_id">Faculty ID</label>
                        <input type="text" id="faculty_id" name="faculty_id" required>
                    </div>
                    <div class="form-group">
                        <label for="hire_date">Hire Date</label>
                        <input type="date" id="hire_date" name="hire_date" required>
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
                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" required>
                    </div>
                    <div class="form-group">
                        <label for="designation">Designation</label>
                        <input type="text" id="designation" name="designation" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="salary">Salary</label>
                        <input type="number" id="salary" name="salary" step="0.01" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addFacultyModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Faculty</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Faculty Modal -->
    <div id="editFacultyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Faculty</h3>
                <span class="close" onclick="closeModal('editFacultyModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_faculty_id">Faculty ID</label>
                        <input type="text" id="edit_faculty_id" name="faculty_id" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_hire_date">Hire Date</label>
                        <input type="date" id="edit_hire_date" name="hire_date" required>
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
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_department">Department</label>
                        <input type="text" id="edit_department" name="department" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_designation">Designation</label>
                        <input type="text" id="edit_designation" name="designation" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_phone">Phone</label>
                        <input type="tel" id="edit_phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_salary">Salary</label>
                        <input type="number" id="edit_salary" name="salary" step="0.01" required>
                    </div>
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
                        <option value="Retired">Retired</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editFacultyModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Faculty</button>
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
                <p>Are you sure you want to delete this faculty member? This action cannot be undone.</p>
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
        
        function editFaculty(faculty) {
            document.getElementById('edit_id').value = faculty.id;
            document.getElementById('edit_faculty_id').value = faculty.faculty_id;
            document.getElementById('edit_first_name').value = faculty.first_name;
            document.getElementById('edit_last_name').value = faculty.last_name;
            document.getElementById('edit_date_of_birth').value = faculty.date_of_birth;
            document.getElementById('edit_gender').value = faculty.gender;
            document.getElementById('edit_phone').value = faculty.phone;
            document.getElementById('edit_address').value = faculty.address;
            document.getElementById('edit_department').value = faculty.department;
            document.getElementById('edit_designation').value = faculty.designation;
            document.getElementById('edit_hire_date').value = faculty.hire_date;
            document.getElementById('edit_salary').value = faculty.salary;
            document.getElementById('edit_status').value = faculty.status;
            openModal('editFacultyModal');
        }
        
        function deleteFaculty(id) {
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
        .status-retired { background: #d1ecf1; color: #0c5460; }
        
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
