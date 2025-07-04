# 📚 PlaceVIT - Campus Placement Management System

## Project Overview

PlaceVIT is a comprehensive web-based platform designed to streamline and manage campus placement activities for students, companies, and placement coordinators. The system provides a centralized portal for job postings, student applications, profile management, and real-time status tracking, making the entire placement process efficient and transparent for all stakeholders.

The project is built using PHP and leverages a modular structure to separate concerns for students, companies, and placement administrators. It features secure authentication, role-based dashboards, and a user-friendly interface for seamless navigation.

---

## 🚀 How to Run the Project

### Prerequisites
- PHP 7.x or higher
- MySQL or compatible database
- Web server (e.g., Apache, Nginx, or XAMPP/WAMP/LAMP stack)

### Setup Instructions

1. **Clone or Download the Repository**
   ```bash
   git clone <repository-url>
   ```
2. **Database Setup**
   - Import the SQL schema and initial data:
     - Open `database/setup.php` and follow the instructions to set up the database.
   - Update database credentials in `includes/config.php` as needed.
3. **Configure Web Server**
   - Set the project root as the web server's document root or place the files in the appropriate directory (e.g., `htdocs` for XAMPP).
4. **Access the Application**
   - Open your browser and navigate to `http://localhost/PlaceVIT` (or the configured path).

---

## 🛠️ Tech Stack

- **Backend:** PHP 
- **Frontend:** HTML, CSS (BOOTSTRAP), JS
- **Database:** MySQL
- **Web Server:** Apache/Nginx/XAMPP/WAMP/LAMP

---

## ✨ Key Features

### For Students
- Register and complete profile
- Browse and apply for job opportunities with a single click
- Track application status
- View and edit personal profile

### For Placement Cell (CDC)
- Post new job opportunities
- View and manage student applications
- Update application statuses in bulk
- View student profiles

### General
- Secure authentication for all users
- Role-based dashboards and navigation
- Responsive and clean UI
- Modular codebase for easy maintenance

---

## 🔒 Security & Best Practices
- Passwords are securely handled 
- Input validation and sanitization
- Session management for authentication

---

## 📈 Future Enhancements
- Automated email notifications
- Analytics and reporting for placement statistics
- Enhanced search and filtering for jobs and students

---

