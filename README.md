# College ERP Management System

A comprehensive Enterprise Resource Planning (ERP) system designed for colleges and educational institutions. Built with modern web technologies including HTML5, CSS3, JavaScript, PHP, and MySQL.

## Features

### 🔐 Authentication System
- User registration and login
- Role-based access control (Admin, Faculty, Student)
- Secure password hashing
- Session management

### 👨‍🎓 Student Management
- Add, edit, and delete student records
- Student profile management
- Enrollment tracking
- Student status management

### 👨‍🏫 Faculty Management
- Faculty member registration and management
- Department assignment
- Salary and designation tracking
- Faculty status management

### 📚 Course Management
- Course creation and management
- Faculty assignment to courses
- Semester and academic year tracking
- Course status management

### 🏢 Department Management
- Department creation and management
- Head of department assignment
- Department code and description management

### 📅 Attendance Tracking
- Daily attendance marking
- Multiple attendance statuses (Present, Absent, Late, Excused)
- Course-wise attendance tracking
- Student and faculty views

### 📊 Grade Management
- Grade entry and management
- Multiple assignment types (Quiz, Midterm, Final, Assignment, Project)
- Automatic grade calculation
- Grade history tracking

### 📈 Dashboard & Analytics
- Role-based dashboard views
- Statistics and metrics
- Recent activities tracking
- Quick access to important functions

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: XAMPP (Apache, MySQL, PHP)
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome

## Installation Instructions

### Prerequisites
- XAMPP Server installed on your system
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Steps

1. **Download and Install XAMPP**
   - Download XAMPP from https://www.apachefriends.org/
   - Install XAMPP on your system
   - Start Apache and MySQL services

2. **Clone/Download the Project**
   - Download the project files
   - Extract to `C:\xampp\htdocs\college-erp\` (or your preferred location)

3. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `college_erp`
   - Import the SQL schema from `database/schema.sql`
   - The database will be created with sample data

4. **Configuration**
   - Update database credentials in `config/database.php` if needed
   - Default credentials (for XAMPP):
     - Host: localhost
     - Username: root
     - Password: (empty)
     - Database: college_erp

5. **Access the Application**
   - Open your web browser
   - Navigate to `http://localhost/college-erp/`
   - Use the default admin credentials:
     - Username: admin
     - Password: password

## Default Login Credentials

### Admin Account
- **Username**: admin
- **Password**: password
- **Role**: Administrator

## User Roles and Permissions

### Administrator
- Full access to all modules
- Student, faculty, and course management
- Department management
- System-wide attendance and grade management
- Notice management

### Faculty
- View assigned courses
- Mark attendance for their courses
- Enter and manage grades
- View student information for their courses

### Student
- View enrolled courses
- Check personal attendance records
- View grades and academic progress
- Access notices and announcements

## File Structure

```
college-erp/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── auth/
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── students/
│   └── index.php
├── faculty/
│   └── index.php
├── courses/
│   └── index.php
├── departments/
│   └── index.php
├── attendance/
│   └── index.php
├── grades/
│   └── index.php
├── notices/
│   └── index.php
├── index.php
├── register.php
├── dashboard.php
└── README.md
```

## Key Features

### Responsive Design
- Mobile-friendly interface
- Works on desktop, tablet, and mobile devices
- Modern and intuitive user interface

### Security Features
- Password hashing using PHP's password_hash()
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session-based authentication

### Database Design
- Normalized database structure
- Foreign key relationships
- Proper indexing for performance
- Data integrity constraints

## Usage Guide

### For Administrators
1. **Student Management**: Add new students, update information, manage enrollment status
2. **Faculty Management**: Register faculty members, assign departments, manage roles
3. **Course Management**: Create courses, assign faculty, set academic details
4. **Department Management**: Organize academic departments and assign heads
5. **Attendance Tracking**: Monitor attendance across all courses
6. **Grade Management**: Oversee grading system and academic progress

### For Faculty
1. **Course Overview**: View assigned courses and enrolled students
2. **Attendance Marking**: Mark daily attendance for students
3. **Grade Entry**: Enter grades for assignments, quizzes, and exams
4. **Student Progress**: Track student performance and attendance

### For Students
1. **Course Information**: View enrolled courses and schedules
2. **Attendance Records**: Check personal attendance history
3. **Grade Tracking**: Monitor academic progress and grades
4. **Notices**: Access important announcements and updates

## Customization

### Adding New Features
- Extend the database schema in `database/schema.sql`
- Create new PHP files following the existing structure
- Update the navigation menu in dashboard files
- Add corresponding JavaScript functionality

### Styling Customization
- Modify `assets/css/style.css` for visual changes
- Update color schemes, fonts, and layouts
- Add new CSS classes for custom components

### Database Modifications
- Update table structures in `database/schema.sql`
- Modify PHP files to handle new database fields
- Update forms and display logic accordingly

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP services are running
   - Verify database credentials in `config/database.php`
   - Ensure database `college_erp` exists

2. **Login Issues**
   - Use default admin credentials: admin/password
   - Check if users table has data
   - Verify session configuration

3. **Permission Errors**
   - Check file permissions on the server
   - Ensure web server has read/write access
   - Verify XAMPP configuration

4. **Page Not Found**
   - Check if files are in correct directory
   - Verify Apache is running
   - Check URL path and file names

## Support and Maintenance

### Regular Maintenance
- Backup database regularly
- Update PHP and MySQL versions
- Monitor system performance
- Review and update security measures

### Security Considerations
- Change default passwords
- Regular security updates
- Monitor access logs
- Implement additional security measures as needed

## License

This project is open source and available under the MIT License.

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bugs and feature requests.

## Contact

For support or questions, please contact the development team or create an issue in the project repository.

---

**Note**: This is a basic ERP system suitable for small to medium-sized educational institutions. For production use, consider additional security measures, performance optimizations, and feature enhancements based on specific requirements.
