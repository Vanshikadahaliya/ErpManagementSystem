<?php
require_once '../config/database.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../index.php');
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                addDepartment($db);
                break;
            case 'edit':
                editDepartment($db);
                break;
            case 'delete':
                deleteDepartment($db);
                break;
        }
    }
}

$departments_query = "SELECT d.*, f.first_name, f.last_name FROM departments d 
                      LEFT JOIN faculty f ON d.head_faculty_id = f.id 
                      ORDER BY d.name";
$departments_stmt = $db->prepare($departments_query);
$departments_stmt->execute();
$departments = $departments_stmt->fetchAll();

$faculty_query = "SELECT id, first_name, last_name FROM faculty WHERE status = 'Active' ORDER BY first_name";
$faculty_stmt = $db->prepare($faculty_query);
$faculty_stmt->execute();
$faculty_list = $faculty_stmt->fetchAll();

function addDepartment($db) {
    $name = sanitizeInput($_POST['name']);
    $code = sanitizeInput($_POST['code']);
    $description = sanitizeInput($_POST['description']);
    $head_faculty_id = !empty($_POST['head_faculty_id']) ? (int)$_POST['head_faculty_id'] : null;
    
    $check_query = "SELECT id FROM departments WHERE code = :code";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':code', $code);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = 'Department code already exists';
        return;
    }
    
    $insert_query = "INSERT INTO departments (name, code, description, head_faculty_id) 
                     VALUES (:name, :code, :description, :head_faculty_id)";
    
    $stmt = $db->prepare($insert_query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':code', $code);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':head_faculty_id', $head_faculty_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Department added successfully';
    } else {
        $_SESSION['error'] = 'Failed to add department';
    }
}

function editDepartment($db) {
    $id = (int)$_POST['id'];
    $name = sanitizeInput($_POST['name']);
    $code = sanitizeInput($_POST['code']);
    $description = sanitizeInput($_POST['description']);
    $head_faculty_id = !empty($_POST['head_faculty_id']) ? (int)$_POST['head_faculty_id'] : null;
    
    $update_query = "UPDATE departments SET 
                     name = :name, 
                     code = :code, 
                     description = :description, 
                     head_faculty_id = :head_faculty_id
                     WHERE id = :id";
    
    $stmt = $db->prepare($update_query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':code', $code);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':head_faculty_id', $head_faculty_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Department updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update department';
    }
}

function deleteDepartment($db) {
    $id = (int)$_POST['id'];
    
    $delete_query = "DELETE FROM departments WHERE id = :id";
    $stmt = $db->prepare($delete_query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Department deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete department';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management - College ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-graduation-cap"></i> College ERP</h2>
                <p>Department Management</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="../students/index.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="../faculty/index.php"><i class="fas fa-chalkboard-teacher"></i> Faculty</a></li>
                <li><a href="../courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="index.php" class="active"><i class="fas fa-building"></i> Departments</a></li>
                <li><a href="../attendance/index.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="../grades/index.php"><i class="fas fa-chart-line"></i> Grades</a></li>
                <li><a href="../notices/index.php"><i class="fas fa-bullhorn"></i> Notices</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Department Management</h1>
                <button class="btn btn-primary" onclick="openModal('addDepartmentModal')">
                    <i class="fas fa-plus"></i> Add Department
                </button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Departments List</h3>
                    <span>Total: <?php echo count($departments); ?> departments</span>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Head of Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($departments)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No departments found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($departments as $dept): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dept['code']); ?></td>
                                <td><?php echo htmlspecialchars($dept['name']); ?></td>
                                <td><?php echo htmlspecialchars($dept['description']); ?></td>
                                <td><?php echo htmlspecialchars(($dept['first_name'] ?? '') . ' ' . ($dept['last_name'] ?? '') ?: 'Not Assigned'); ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="editDepartment(<?php echo htmlspecialchars(json_encode($dept)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteDepartment(<?php echo $dept['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Department Modal -->
    <div id="addDepartmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Department</h3>
                <span class="close" onclick="closeModal('addDepartmentModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Department Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="code">Department Code</label>
                        <input type="text" id="code" name="code" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="head_faculty_id">Head of Department</label>
                    <select id="head_faculty_id" name="head_faculty_id">
                        <option value="">Select Faculty</option>
                        <?php foreach ($faculty_list as $faculty): ?>
                        <option value="<?php echo $faculty['id']; ?>">
                            <?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addDepartmentModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Department</button>
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
        
        function editDepartment(dept) {
            // Implementation for editing departments
            alert('Edit functionality can be added here');
        }
        
        function deleteDepartment(id) {
            if (confirm('Are you sure you want to delete this department?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
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
</body>
</html>
