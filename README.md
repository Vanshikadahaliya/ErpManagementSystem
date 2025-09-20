# College ERP Management System

A web-based College ERP (Enterprise Resource Planning) system for managing students, faculty, courses, attendance, grades, and notices in an educational institution.

## Features

- **User Authentication**: Secure login for Admin, Faculty, and Students.
- **Student Management**: Add, edit, view, and delete student records.
- **Faculty Management**: Manage faculty profiles and assignments.
- **Course Management**: Create and assign courses to faculty and students.
- **Department Management**: Organize and manage academic departments.
- **Attendance Tracking**: Mark and view attendance for students and faculty.
- **Grade Management**: Enter and view grades for students.
- **Notices**: Post and view important announcements.
- **Role-Based Dashboards**: Custom dashboards for Admin, Faculty, and Students.

## Technologies Used

- **Backend**: PHP
- **Frontend**: HTML, CSS, JavaScript
- **Database**: MySQL
- **Web Server**: Apache (XAMPP recommended)

## Installation

1. **Clone the repository**
   ```
   git clone https://github.com/yourusername/college-erp.git
   ```

2. **Copy to XAMPP htdocs folder**
   ```
   cp -r college-erp c:/xampp/htdocs/
   ```

3. **Import the Database**
   - Open phpMyAdmin at [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Create a new database (e.g., `college_erp`)
   - Import the `database/schema.sql` file

4. **Configure Database Connection**
   - Edit `config/database.php` and update your MySQL credentials if needed.

5. **Run the Application**
   - Start Apache and MySQL from XAMPP Control Panel
   - Visit [http://localhost/college-erp](http://localhost/college-erp) in your browser

## Default Admin Credentials

- **Username:** admin
- **Password:** admin123

> Change the default password after first login.

## Project Structure

```
college-erp/
├── assets/           # CSS, JS, images
├── auth/             # Login, register, logout
├── config/           # Database configuration
├── database/         # SQL schema
├── faculty/          # Faculty modules
├── student/          # Student modules
├── admin/            # Admin modules
├── index.php         # Entry point
└── README.md
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Create a new Pull Request

## License

This project is licensed under the MIT License.

---

**Developed by [Vanshika Dahaliya]**
