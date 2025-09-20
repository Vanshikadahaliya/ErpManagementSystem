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
    <title>Attendance - College ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Inline modal styles to ensure they load */
        .modal {
            position: fixed !important;
            z-index: 1000 !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: rgba(0,0,0,0.5) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .modal-content {
            background-color: white !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
            width: 90% !important;
            max-width: 600px !important;
            max-height: 90vh !important;
            overflow-y: auto !important;
        }
        
        .modal-header {
            padding: 20px !important;
            border-bottom: 1px solid #e2e8f0 !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
        }
        
        .close {
            color: #a0aec0 !important;
            font-size: 28px !important;
            font-weight: bold !important;
            cursor: pointer !important;
        }
        
        .close:hover {
            color: #e53e3e !important;
        }
        
        .close:active {
            transform: scale(0.95);
        }
        
        /* Ensure close button is clickable */
        .close {
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
        }
        
        .modal-body {
            padding: 20px !important;
        }
        
        .modal-footer {
            padding: 20px !important;
            border-top: 1px solid #e2e8f0 !important;
            display: flex !important;
            justify-content: flex-end !important;
            gap: 10px !important;
        }
        
        .attendance-list {
            max-height: 300px !important;
            overflow-y: auto !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 10px !important;
        }
        
        .student-attendance-item {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 10px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        
        .attendance-options {
            display: flex !important;
            gap: 15px !important;
        }
        
        .attendance-options label {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
            cursor: pointer !important;
            margin: 0 !important;
        }
    </style>
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
                <li><a href="attendance.php" class="active"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                <li><a href="grades.php"><i class="fas fa-chart-line"></i> Grades</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Attendance Management</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($username); ?> (Faculty)</span>
                    <a href="../auth/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Attendance Content -->
            <div class="card">
                <div class="card-header">
                    <h3>Mark Attendance</h3>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button class="btn btn-primary" onclick="showMarkAttendanceModal()">
                            <i class="fas fa-plus"></i> Mark New Attendance
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
                                <th>Date</th>
                                <th>Course</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Sep 19, 2025</td>
                                <td>CS101 - Introduction to Programming</td>
                                <td>42</td>
                                <td>3</td>
                                <td>45</td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="viewAttendanceDetails('att_001')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-warning btn-sm" onclick="editAttendance('att_001')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Sep 18, 2025</td>
                                <td>CS201 - Data Structures</td>
                                <td>35</td>
                                <td>3</td>
                                <td>38</td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="viewAttendanceDetails('att_002')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-warning btn-sm" onclick="editAttendance('att_002')">
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
    
    <!-- Simple Mark Attendance Modal -->
    <div id="markAttendanceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 10px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                <h3 style="margin: 0;">Mark Attendance</h3>
                <button onclick="closeMarkAttendanceModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
            </div>
            
            <form id="attendanceForm">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Date</label>
                    <input type="date" id="attendanceDate" name="date" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Course</label>
                    <select id="attendanceCourse" name="course" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Select Course</option>
                        <option value="CS101">CS101 - Introduction to Programming</option>
                        <option value="CS201">CS201 - Data Structures</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Time</label>
                    <input type="time" id="attendanceTime" name="time" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Students Attendance</label>
                    <div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; max-height: 200px; overflow-y: auto;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; border-bottom: 1px solid #f0f0f0;">
                            <span>John Doe (STU001)</span>
                            <div>
                                <label style="margin-right: 15px;"><input type="radio" name="stu001" value="present" checked> Present</label>
                                <label><input type="radio" name="stu001" value="absent"> Absent</label>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; border-bottom: 1px solid #f0f0f0;">
                            <span>Jane Smith (STU002)</span>
                            <div>
                                <label style="margin-right: 15px;"><input type="radio" name="stu002" value="present" checked> Present</label>
                                <label><input type="radio" name="stu002" value="absent"> Absent</label>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px;">
                            <span>Mike Johnson (STU003)</span>
                            <div>
                                <label style="margin-right: 15px;"><input type="radio" name="stu003" value="present" checked> Present</label>
                                <label><input type="radio" name="stu003" value="absent"> Absent</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Notes (Optional)</label>
                    <textarea id="attendanceNotes" name="notes" rows="3" placeholder="Any additional notes..." style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"></textarea>
                </div>
            </form>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee; padding-top: 15px;">
                <button type="button" onclick="closeMarkAttendanceModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="button" onclick="submitAttendance()" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
            </div>
        </div>
    </div>
    
    <!-- Simple View Attendance Details Modal -->
    <div id="viewAttendanceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 10px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                <h3 style="margin: 0;">Attendance Details</h3>
                <button onclick="closeViewAttendanceModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; padding: 15px; background-color: #f8fafc; border-radius: 8px;">
                <div>
                    <strong style="color: #4a5568; font-size: 0.9rem;">Date:</strong>
                    <div style="color: #2d3748; font-weight: 500;" id="viewDate">Sep 19, 2025</div>
                </div>
                <div>
                    <strong style="color: #4a5568; font-size: 0.9rem;">Course:</strong>
                    <div style="color: #2d3748; font-weight: 500;" id="viewCourse">CS101 - Introduction to Programming</div>
                </div>
                <div>
                    <strong style="color: #4a5568; font-size: 0.9rem;">Time:</strong>
                    <div style="color: #2d3748; font-weight: 500;" id="viewTime">9:00 AM</div>
                </div>
                <div>
                    <strong style="color: #4a5568; font-size: 0.9rem;">Total Students:</strong>
                    <div style="color: #2d3748; font-weight: 500;" id="viewTotal">45</div>
                </div>
                <div>
                    <strong style="color: #4a5568; font-size: 0.9rem;">Present:</strong>
                    <div style="color: #28a745; font-weight: 500;" id="viewPresent">42</div>
                </div>
                <div>
                    <strong style="color: #4a5568; font-size: 0.9rem;">Absent:</strong>
                    <div style="color: #dc3545; font-weight: 500;" id="viewAbsent">3</div>
                </div>
            </div>
            
            <div>
                <h4 style="color: #2d3748; margin-bottom: 15px; font-size: 1.1rem;">Student List</h4>
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9; font-weight: 600; color: #333;">Student ID</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9; font-weight: 600; color: #333;">Name</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9; font-weight: 600; color: #333;">Status</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9; font-weight: 600; color: #333;">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">STU001</td>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">John Doe</td>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">
                                <span style="display: inline-block; padding: 4px 8px; font-size: 0.75rem; font-weight: 600; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #28a745; color: white;">Present</span>
                            </td>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">9:05 AM</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">STU002</td>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">Jane Smith</td>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">
                                <span style="display: inline-block; padding: 4px 8px; font-size: 0.75rem; font-weight: 600; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #28a745; color: white;">Present</span>
                            </td>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">9:02 AM</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">STU003</td>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">Mike Johnson</td>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">
                                <span style="display: inline-block; padding: 4px 8px; font-size: 0.75rem; font-weight: 600; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #dc3545; color: white;">Absent</span>
                            </td>
                            <td style="padding: 15px; text-align: left; border-bottom: 1px solid #e1e5e9;">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee; padding-top: 15px; margin-top: 20px;">
                <button type="button" onclick="closeViewAttendanceModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                <button type="button" onclick="editAttendanceFromView()" style="padding: 8px 16px; background: #ffc107; color: #212529; border: none; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-edit"></i> Edit Attendance
                </button>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Debug function to check if JavaScript is working
        console.log('Faculty attendance JavaScript loaded');
        
        // Test function to verify JavaScript is working
        function testFunction() {
            alert('JavaScript is working! The modals should now be functional.');
            console.log('Test function called successfully');
        }
        
        // Simple modal test
        function showSimpleModal() {
            alert('Simple modal test - JavaScript is working!\n\nNow try the "Mark New Attendance" button.');
        }
        
        // Simple Modal Functions
        function showMarkAttendanceModal() {
            console.log('Opening modal');
            document.getElementById('markAttendanceModal').style.display = 'block';
            
            // Set today's date
            document.getElementById('attendanceDate').value = new Date().toISOString().split('T')[0];
            
            // Set current time
            const now = new Date();
            document.getElementById('attendanceTime').value = now.toTimeString().slice(0, 5);
            
            // Reset modal title and button text for new attendance
            const modalTitle = document.querySelector('#markAttendanceModal h3');
            if (modalTitle) {
                modalTitle.textContent = 'Mark Attendance';
            }
            
            const saveBtn = document.querySelector('#markAttendanceModal .btn-primary');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Attendance';
            }
        }
        
        function closeMarkAttendanceModal() {
            console.log('Closing modal');
            document.getElementById('markAttendanceModal').style.display = 'none';
            document.getElementById('attendanceForm').reset();
        }
        
        function submitAttendance() {
            console.log('Submitting attendance');
            
            // Get form data with error handling
            const course = document.getElementById('attendanceCourse');
            const date = document.getElementById('attendanceDate');
            const time = document.getElementById('attendanceTime');
            
            // Validate form data
            if (!course || !course.value) {
                alert('Please select a course');
                return;
            }
            if (!date || !date.value) {
                alert('Please select a date');
                return;
            }
            if (!time || !time.value) {
                alert('Please select a time');
                return;
            }
            
            // Get values safely
            const courseValue = course.value;
            const dateValue = date.value;
            const timeValue = time.value;
            
            // Count present/absent students from individual radio buttons
            let presentCount = 0;
            let absentCount = 0;
            
            // Check each student's attendance
            const studentRadios = document.querySelectorAll('input[name^="stu"]:checked');
            studentRadios.forEach(radio => {
                if (radio.value === 'present') {
                    presentCount++;
                } else if (radio.value === 'absent') {
                    absentCount++;
                }
            });
            
            // If no students are marked, use default values
            if (presentCount === 0 && absentCount === 0) {
                presentCount = Math.floor(Math.random() * 10) + 35;
                absentCount = 45 - presentCount;
            }
            
            const newRecord = {
                id: 'att_' + Date.now(),
                course: courseValue,
                date: dateValue,
                time: timeValue,
                present: presentCount.toString(),
                absent: absentCount.toString(),
                total: '45'
            };
            
            // Add to table
            addAttendanceToTable(newRecord);
            
            alert('Attendance saved successfully!');
            closeMarkAttendanceModal();
        }
        
        function addAttendanceToTable(record) {
            const tbody = document.querySelector('.table tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${record.course}</td>
                <td>${record.date}</td>
                <td>${record.time}</td>
                <td>${record.present}</td>
                <td>${record.absent}</td>
                <td>${record.total}</td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="viewAttendanceDetails('${record.id}')">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="editAttendance('${record.id}')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </td>
            `;
            tbody.insertBefore(newRow, tbody.firstChild);
        }
        
        // View Attendance Details
        function viewAttendanceDetails(attendanceId) {
            console.log('Viewing attendance details:', attendanceId);
            document.getElementById('viewAttendanceModal').style.display = 'block';
            
            // Populate with sample data based on attendanceId
            if (attendanceId === 'att_001') {
                document.getElementById('viewDate').textContent = 'Sep 19, 2025';
                document.getElementById('viewCourse').textContent = 'CS101 - Introduction to Programming';
                document.getElementById('viewTime').textContent = '9:00 AM';
                document.getElementById('viewTotal').textContent = '45';
                document.getElementById('viewPresent').textContent = '42';
                document.getElementById('viewAbsent').textContent = '3';
            } else if (attendanceId === 'att_002') {
                document.getElementById('viewDate').textContent = 'Sep 18, 2025';
                document.getElementById('viewCourse').textContent = 'CS201 - Data Structures';
                document.getElementById('viewTime').textContent = '2:00 PM';
                document.getElementById('viewTotal').textContent = '38';
                document.getElementById('viewPresent').textContent = '35';
                document.getElementById('viewAbsent').textContent = '3';
            }
        }
        
        function closeViewAttendanceModal() {
            console.log('Closing view modal');
            document.getElementById('viewAttendanceModal').style.display = 'none';
        }
        
        // Edit Attendance
        function editAttendance(attendanceId) {
            console.log('Editing attendance:', attendanceId);
            // Open the mark attendance modal in edit mode
            showMarkAttendanceModal();
            // Pre-fill the form with existing data
            populateEditForm(attendanceId);
        }
        
        function editAttendanceFromView() {
            closeViewAttendanceModal();
            // Open edit form
            showMarkAttendanceModal();
            // Pre-fill with current data
            populateEditForm('att_001');
        }
        
        function populateEditForm(attendanceId) {
            // Simulate loading existing data
            setTimeout(() => {
                document.getElementById('attendanceDate').value = '2025-09-19';
                document.getElementById('attendanceCourse').value = 'CS101';
                document.getElementById('attendanceTime').value = '09:00';
                document.getElementById('attendanceNotes').value = 'Regular class session';
                
                // Update modal title to indicate edit mode
                const modalTitle = document.querySelector('#markAttendanceModal .modal-header h3');
                if (modalTitle) {
                    modalTitle.textContent = 'Edit Attendance';
                }
                
                // Update save button text
                const saveBtn = document.querySelector('#markAttendanceModal .btn-primary');
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Attendance';
                }
            }, 100);
        }
        
        // Simple force close function
        function forceCloseAllModals() {
            console.log('Force closing all modals');
            document.getElementById('markAttendanceModal').style.display = 'none';
            document.getElementById('viewAttendanceModal').style.display = 'none';
        }
    </script>
</body>
</html>
