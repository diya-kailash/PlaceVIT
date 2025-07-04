<?php
$host = "localhost";
$username = "mwa";
$password = "mwa";
$dbname = "placement_vit";

$conn = new mysqli($host, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}
$conn->select_db($dbname);

// Students table
$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_number VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    dob DATE,
    age INT,
    hometown VARCHAR(100),
    college VARCHAR(100),
    degree VARCHAR(50),
    branch VARCHAR(100),
    semester INT,
    cgpa FLOAT,
    marks_12th FLOAT,
    marks_10th FLOAT,
    resume_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating students table: " . $conn->error);
}

// CDC Staff table
$sql = "CREATE TABLE IF NOT EXISTS cdc_staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    designation VARCHAR(100),
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating cdc_staff table: " . $conn->error);
}

// Job Opportunities table
$sql = "CREATE TABLE IF NOT EXISTS job_opportunities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    role VARCHAR(100) NOT NULL,
    ctc VARCHAR(50),
    job_description_path VARCHAR(255),
    deadline DATETIME NOT NULL,
    posted_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES cdc_staff(id)
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating job_opportunities table: " . $conn->error);
}

// Applications table
$sql = "CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    job_id INT,
    status ENUM('applied', 'shortlisted', 'selected', 'rejected') DEFAULT 'applied',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (job_id) REFERENCES job_opportunities(id),
    UNIQUE KEY (student_id, job_id)
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating applications table: " . $conn->error);
}

echo "Database setup completed successfully!";
$conn->close();
?>
