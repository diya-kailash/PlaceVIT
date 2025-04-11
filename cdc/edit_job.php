<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if CDC staff is logged in
if (!isset($_SESSION['cdc_id']) || $_SESSION['user_type'] !== 'cdc') {
    header("Location: login.php");
    exit();
}

$cdc_id = $_SESSION['cdc_id'];
$success = $error = '';

// Check if job ID is provided
if (!isset($_GET['id'])) {
    header("Location: job_opportunities.php");
    exit();
}

$job_id = intval($_GET['id']);

// Fetch job details
$job_query = "SELECT * FROM job_opportunities WHERE id = ? AND posted_by = ?";
$stmt = $conn->prepare($job_query);
$stmt->bind_param("ii", $job_id, $cdc_id);
$stmt->execute();
$result = $stmt->get_result();

// If job not found or doesn't belong to this CDC staff
if ($result->num_rows == 0) {
    header("Location: job_opportunities.php");
    exit();
}

$job = $result->fetch_assoc();

// Handle form submission for updating job
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $ctc = mysqli_real_escape_string($conn, $_POST['ctc']);
    $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
    
    // Validate required fields
    if (empty($company_name) || empty($role) || empty($ctc) || empty($deadline)) {
        $error = "All fields are required.";
    } else {
        // Handle job description file upload (if new file provided)
        $jd_path = $job['job_description_path']; // Keep existing path by default
        
        if (isset($_FILES['job_description']) && $_FILES['job_description']['error'] == 0) {
            $allowed_ext = ['pdf', 'doc', 'docx'];
            $file_name = $_FILES['job_description']['name'];
            $file_size = $_FILES['job_description']['size'];
            $file_tmp = $_FILES['job_description']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_ext)) {
                $error = "Job description must be in PDF, DOC, or DOCX format.";
            } elseif ($file_size > 5242880) { // 5MB max
                $error = "Job description file size must be less than 5MB.";
            } else {
                $new_file_name = "jd_" . preg_replace('/\s+/', '', $company_name) . "" . time() . "." . $file_ext;
                $upload_path = "../uploads/job_descriptions/";
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $new_jd_path = $upload_path . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $new_jd_path)) {
                    // Delete old file if exists
                    if (!empty($jd_path) && file_exists($jd_path) && $jd_path != $new_jd_path) {
                        unlink($jd_path);
                    }
                    $jd_path = $new_jd_path;
                } else {
                    $error = "Failed to upload job description. Please try again.";
                }
            }
        }
        
        // If no errors, update job opportunity
        if (empty($error)) {
            $update_query = "UPDATE job_opportunities SET 
                            company_name = ?, 
                            role = ?, 
                            ctc = ?, 
                            job_description_path = ?, 
                            deadline = ? 
                            WHERE id = ? AND posted_by = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sssssii", $company_name, $role, $ctc, $jd_path, $deadline, $job_id, $cdc_id);
            
            if ($stmt->execute()) {
                $success = "Job opportunity updated successfully!";
                
                // Refresh job data
                $stmt = $conn->prepare($job_query);
                $stmt->bind_param("ii", $job_id, $cdc_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $job = $result->fetch_assoc();
            } else {
                $error = "Error updating job opportunity: " . $stmt->error;
            }
        }
    }
}

// Format deadline for datetime-local input
$deadline_formatted = date('Y-m-d\TH:i', strtotime($job['deadline']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job Opportunity - VIT Placement Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">placeVIT</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="job_opportunities.php">Job Opportunities</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="applications.php">Applications</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['cdc_name']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Edit Job Opportunity</h2>
            </div>
            <div class="col-md-4 text-right">
                <a href="job_opportunities.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Jobs
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success; ?>
                        <div class="mt-2">
                            <a href="job_opportunities.php" class="btn btn-sm btn-primary">View All Jobs</a>
                            <a href="view_job.php?id=<?php echo $job_id; ?>" class="btn btn-sm btn-outline-primary">View Job Details</a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="edit_job.php?id=<?php echo $job_id; ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($job['company_name']); ?>" placeholder="Enter company name" required>
                        <div class="invalid-feedback">
                            Please provide a company name.
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role Offered</label>
                        <input type="text" class="form-control" id="role" name="role" value="<?php echo htmlspecialchars($job['role']); ?>" placeholder="Enter role/position offered" required>
                        <div class="invalid-feedback">
                            Please provide a role/position.
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="ctc">CTC/Stipend</label>
                        <input type="text" class="form-control" id="ctc" name="ctc" value="<?php echo htmlspecialchars($job['ctc']); ?>" placeholder="Enter CTC or stipend details" required>
                        <div class="invalid-feedback">
                            Please provide CTC/stipend details.
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="job_description">Job Description (PDF, DOC, or DOCX format, max 5MB)</label>
                        <input type="file" class="form-control-file" id="job_description" name="job_description" accept=".pdf,.doc,.docx">
                        <?php if (!empty($job['job_description_path'])): ?>
                            <div class="mt-2">
                                <a href="<?php echo htmlspecialchars($job['job_description_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file-alt"></i> View Current Job Description
                                </a>
                                <small class="text-muted ml-2">Upload a new file to replace the current one.</small>
                            </div>
                        <?php else: ?>
                            <small class="form-text text-muted">No job description currently uploaded. Upload one now.</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="deadline">Application Deadline</label>
                        <input type="datetime-local" class="form-control" id="deadline" name="deadline" value="<?php echo $deadline_formatted; ?>" required>
                        <div class="invalid-feedback">
                            Please provide an application deadline.
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Job Opportunity
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p>&copy; <?php echo date("Y"); ?> VIT Placement Portal. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script>
    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            // Fetch all forms we want to apply validation to
            var forms = document.getElementsByClassName('needs-validation');
            // Loop over them and prevent submission
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
    </script>
</body>
</html>