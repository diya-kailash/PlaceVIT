<?php
require_once '../includes/config.php';

// Check if CDC staff is logged in
if (!isset($_SESSION['cdc_id']) || $_SESSION['user_type'] !== 'cdc') {
    header("Location: login.php");
    exit();
}

$cdc_id = $_SESSION['cdc_id'];
$success = $error = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $ctc = mysqli_real_escape_string($conn, $_POST['ctc']);
    $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
    if (empty($company_name) || empty($role) || empty($ctc) || empty($deadline)) {
        $error = "All fields are required.";
    } else {
        $jd_path = null;
        
        if (isset($_FILES['job_description']) && $_FILES['job_description']['error'] == 0) {
            $allowed_ext = ['pdf', 'doc', 'docx'];
            $file_name = $_FILES['job_description']['name'];
            $file_size = $_FILES['job_description']['size'];
            $file_tmp = $_FILES['job_description']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_ext)) {
                $error = "Job description must be in PDF, DOC, or DOCX format.";
            } elseif ($file_size > 5242880) { 
                $error = "Job description file size must be less than 5MB.";
            } else {
                $new_file_name = "jd_" . preg_replace('/\s+/', '_', $company_name) . "_" . time() . "." . $file_ext;
                $upload_path = "../uploads/job_descriptions/";
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $jd_path = $upload_path . $new_file_name;
                
                if (!move_uploaded_file($file_tmp, $jd_path)) {
                    $error = "Failed to upload job description. Please try again.";
                }
            }
        }
        
        // If no errors, insert job opportunity
        if (empty($error)) {
            $insert_query = "INSERT INTO job_opportunities (company_name, role, ctc, job_description_path, deadline, posted_by) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("sssssi", $company_name, $role, $ctc, $jd_path, $deadline, $cdc_id);
            
            if ($stmt->execute()) {
                $success = "Job opportunity posted successfully!";
            } else {
                $error = "Error posting job opportunity: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job Opportunity - VIT Placement Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
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
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Post New Job Opportunity</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="job_opportunities.php" class="btn btn-sm btn-primary">View All Jobs</a>
                                    <a href="post_job.php" class="btn btn-sm btn-outline-primary">Post Another Job</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="post_job.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="form-group">
                                <label for="company_name">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Enter company name" required>
                                <div class="invalid-feedback">
                                    Please provide a company name.
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role Offered</label>
                                <input type="text" class="form-control" id="role" name="role" placeholder="Enter role/position offered" required>
                                <div class="invalid-feedback">
                                    Please provide a role/position.
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="ctc">CTC/Stipend</label>
                                <input type="text" class="form-control" id="ctc" name="ctc" placeholder="Enter CTC or stipend details" required>
                                <div class="invalid-feedback">
                                    Please provide CTC/stipend details.
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="job_description">Job Description (PDF, DOC, or DOCX format, max 5MB)</label>
                                <input type="file" class="form-control-file" id="job_description" name="job_description" accept=".pdf,.doc,.docx">
                                <small class="form-text text-muted">Upload a detailed job description document.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="deadline">Application Deadline</label>
                                <input type="datetime-local" class="form-control" id="deadline" name="deadline" required>
                                <div class="invalid-feedback">
                                    Please provide an application deadline.
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Post Job Opportunity
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container text-center">
            <p>&copy; <?php echo date("Y"); ?> VIT Placement Portal. All Rights Reserved.</p>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script>
    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
            
            // Set minimum date for deadline
            var deadlineInput = document.getElementById('deadline');
            var now = new Date();
            var year = now.getFullYear();
            var month = (now.getMonth() + 1).toString().padStart(2, '0');
            var day = now.getDate().toString().padStart(2, '0');
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            
            var minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            deadlineInput.setAttribute('min', minDateTime);
        }, false);
    })();
    </script>
</body>
</html>
