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
    <title>Grades - College ERP</title>
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
                <li><a href="students.php"><i class="fas fa-user-graduate"></i> My Students</a></li>
                <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="grades.php" class="active"><i class="fas fa-chart-line"></i> Grades</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Grade Management</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($username); ?> (Faculty)</span>
                    <a href="../auth/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Grades Content -->
            <div class="card">
                <div class="card-header">
                    <h3>Student Grades</h3>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button class="btn btn-primary" onclick="showAddGradesModal()">
                            <i class="fas fa-plus"></i> Add Grades
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="forceCloseAllModals()" title="Force Close All Modals">
                            <i class="fas fa-times"></i> Force Close
                        </button>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Assignment</th>
                                <th>Score</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>STU001</td>
                                <td>John Doe</td>
                                <td>CS101 - Introduction to Programming</td>
                                <td>Mid-term Exam</td>
                                <td>85/100</td>
                                <td>A</td>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="editGrade('grade_001')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>STU002</td>
                                <td>Jane Smith</td>
                                <td>CS101 - Introduction to Programming</td>
                                <td>Mid-term Exam</td>
                                <td>92/100</td>
                                <td>A+</td>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="editGrade('grade_002')">
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
    
    <!-- Add Grades Modal -->
    <div id="addGradesModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 10px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                <h3 style="margin: 0;">Add Grades</h3>
                <button onclick="closeAddGradesModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
            </div>
            
            <form id="gradesForm">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Course</label>
                    <select id="gradesCourse" name="course" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Select Course</option>
                        <option value="CS101">CS101 - Introduction to Programming</option>
                        <option value="CS201">CS201 - Data Structures</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Assignment/Exam</label>
                    <input type="text" id="gradesAssignment" name="assignment" placeholder="e.g., Mid-term Exam, Assignment 1" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Date</label>
                    <input type="date" id="gradesDate" name="date" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Total Marks</label>
                    <input type="number" id="gradesTotal" name="total_marks" placeholder="100" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Student Grades</label>
                    <div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; max-height: 300px; overflow-y: auto;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; border-bottom: 1px solid #f0f0f0;">
                            <span>John Doe (STU001)</span>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="number" name="stu001_marks" placeholder="Marks" style="width: 80px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
                                <select name="stu001_grade" style="padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">Grade</option>
                                    <option value="A+">A+</option>
                                    <option value="A">A</option>
                                    <option value="B+">B+</option>
                                    <option value="B">B</option>
                                    <option value="C+">C+</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="F">F</option>
                                </select>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; border-bottom: 1px solid #f0f0f0;">
                            <span>Jane Smith (STU002)</span>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="number" name="stu002_marks" placeholder="Marks" style="width: 80px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
                                <select name="stu002_grade" style="padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">Grade</option>
                                    <option value="A+">A+</option>
                                    <option value="A">A</option>
                                    <option value="B+">B+</option>
                                    <option value="B">B</option>
                                    <option value="C+">C+</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="F">F</option>
                                </select>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px;">
                            <span>Mike Johnson (STU003)</span>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="number" name="stu003_marks" placeholder="Marks" style="width: 80px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
                                <select name="stu003_grade" style="padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">Grade</option>
                                    <option value="A+">A+</option>
                                    <option value="A">A</option>
                                    <option value="B+">B+</option>
                                    <option value="B">B</option>
                                    <option value="C+">C+</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="F">F</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Notes (Optional)</label>
                    <textarea id="gradesNotes" name="notes" rows="3" placeholder="Any additional notes about the grades..." style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"></textarea>
                </div>
            </form>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee; padding-top: 15px;">
                <button type="button" onclick="closeAddGradesModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="button" onclick="submitGrades()" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-save"></i> Save Grades
                </button>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Debug function
        console.log('Faculty grades JavaScript loaded');
        
        // Add Grades Modal Functions
        function showAddGradesModal() {
            console.log('Opening add grades modal');
            document.getElementById('addGradesModal').style.display = 'block';
            
            // Set today's date as default
            document.getElementById('gradesDate').value = new Date().toISOString().split('T')[0];
            
            // Reset modal title and button text for new grades
            const modalTitle = document.querySelector('#addGradesModal h3');
            if (modalTitle) {
                modalTitle.textContent = 'Add Grades';
            }
            
            const saveBtn = document.querySelector('#addGradesModal .btn-primary');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Grades';
            }
        }
        
        function closeAddGradesModal() {
            console.log('Closing add grades modal');
            document.getElementById('addGradesModal').style.display = 'none';
            document.getElementById('gradesForm').reset();
        }
        
        function submitGrades() {
            console.log('Submitting grades');
            
            // Get form data
            const course = document.getElementById('gradesCourse').value;
            const assignment = document.getElementById('gradesAssignment').value;
            const date = document.getElementById('gradesDate').value;
            const totalMarks = document.getElementById('gradesTotal').value;
            
            // Get student grades
            const studentGrades = [];
            const studentInputs = document.querySelectorAll('input[name^="stu"]');
            const studentSelects = document.querySelectorAll('select[name^="stu"]');
            
            for (let i = 0; i < studentInputs.length; i++) {
                const marks = studentInputs[i].value;
                const grade = studentSelects[i].value;
                if (marks || grade) {
                    const studentName = studentInputs[i].name.replace('_marks', '').replace('stu', 'STU');
                    studentGrades.push({
                        id: studentName,
                        name: getStudentName(studentName),
                        marks: marks,
                        grade: grade
                    });
                }
            }
            
            // Add each student grade to table
            studentGrades.forEach(student => {
                addGradeToTable({
                    id: 'grade_' + Date.now() + '_' + student.id,
                    studentId: student.id,
                    studentName: student.name,
                    course: course,
                    assignment: assignment,
                    marks: student.marks,
                    totalMarks: totalMarks,
                    grade: student.grade
                });
            });
            
            alert('Grades saved successfully!');
            closeAddGradesModal();
        }
        
        function getStudentName(studentId) {
            const names = {
                'STU001': 'John Doe',
                'STU002': 'Jane Smith', 
                'STU003': 'Mike Johnson'
            };
            return names[studentId] || 'Unknown Student';
        }
        
        function addGradeToTable(record) {
            const tbody = document.querySelector('.table tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${record.studentId}</td>
                <td>${record.studentName}</td>
                <td>${record.course} - ${getCourseName(record.course)}</td>
                <td>${record.assignment}</td>
                <td>${record.marks}/${record.totalMarks}</td>
                <td>${record.grade}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editGrade('${record.id}')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </td>
            `;
            tbody.insertBefore(newRow, tbody.firstChild);
        }
        
        function getCourseName(courseCode) {
            const courses = {
                'CS101': 'Introduction to Programming',
                'CS201': 'Data Structures'
            };
            return courses[courseCode] || courseCode;
        }
        
        // Edit Grade Function
        function editGrade(gradeId) {
            console.log('Editing grade:', gradeId);
            // Open the add grades modal in edit mode
            showAddGradesModal();
            // Pre-fill the form with existing data
            setTimeout(() => {
                document.getElementById('gradesCourse').value = 'CS101';
                document.getElementById('gradesAssignment').value = 'Mid-term Exam';
                document.getElementById('gradesDate').value = '2025-09-15';
                document.getElementById('gradesTotal').value = '100';
                
                // Update modal title
                const modalTitle = document.querySelector('#addGradesModal h3');
                if (modalTitle) {
                    modalTitle.textContent = 'Edit Grade';
                }
                
                // Update save button text
                const saveBtn = document.querySelector('#addGradesModal .btn-primary');
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Grade';
                }
            }, 100);
        }
        
        // Force close function for debugging
        function forceCloseAllModals() {
            console.log('Force closing all modals');
            document.getElementById('addGradesModal').style.display = 'none';
        }
    </script>
</body>
</html>
