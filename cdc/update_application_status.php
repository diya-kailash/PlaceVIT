<?php
require_once '../includes/config.php';

// Check if CDC staff is logged in
if (!isset($_SESSION['cdc_id']) || $_SESSION['user_type'] !== 'cdc') {
    header("Location: login.php");
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['application_id']) && isset($_POST['status']) && isset($_POST['student_id'])) {
    $application_id = intval($_POST['application_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $student_id = intval($_POST['student_id']);
    $cdc_id = $_SESSION['cdc_id'];
    
    $valid_statuses = ['applied', 'shortlisted', 'selected', 'rejected'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Invalid status value.";
        header("Location: view_student.php?id=" . $student_id);
        exit();
    }
    
    // Check if the application belongs to a job posted by this CDC staff
    $check_query = "SELECT a.* FROM applications a 
                   JOIN job_opportunities j ON a.job_id = j.id 
                   WHERE a.id = ? AND j.posted_by = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $application_id, $cdc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update application status
        $update_query = "UPDATE applications SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $status, $application_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Application status updated successfully.";
        } else {
            $_SESSION['error'] = "Error updating application status: " . $stmt->error;
        }
    } else {
        $_SESSION['error'] = "You don't have permission to update this application.";
    }
    
    header("Location: view_student.php?id=" . $student_id);
    exit();
} else {
    header("Location: dashboard.php");
    exit();
}
?>
