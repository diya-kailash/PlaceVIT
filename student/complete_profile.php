<?php
require_once '../includes/config.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Get student data
$student_id = $_SESSION['student_id'];
$query = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Check if profile is already complete, if so redirect to dashboard
if (!empty($student['dob']) && !empty($student['college']) && 
    !empty($student['degree']) && !empty($student['branch']) && 
    $student['branch'] !== '0' && !empty($student['cgpa'])) {
    header("Location: dashboard.php");
    exit();
}

// Debug the retrieved branch value
error_log("Retrieved branch value: " . (isset($student['branch']) ? $student['branch'] : 'not set'));

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $hometown = mysqli_real_escape_string($conn, $_POST['hometown']);
    $college = mysqli_real_escape_string($conn, $_POST['college']);
    $degree = mysqli_real_escape_string($conn, $_POST['degree']);
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $semester = isset($_POST['semester']) ? intval($_POST['semester']) : null;
    $cgpa = isset($_POST['cgpa']) ? floatval($_POST['cgpa']) : null;
    $marks_12th = isset($_POST['marks_12th']) ? floatval($_POST['marks_12th']) : null;
    $marks_10th = isset($_POST['marks_10th']) ? floatval($_POST['marks_10th']) : null;
    $error = "";
    
    // Validate CGPA
    if ($cgpa < 0 || $cgpa > 10) {
        $error .= "CGPA must be between 0 and 10.<br>";
    }
    
    // Validate 12th marks
    if ($marks_12th < 0 || $marks_12th > 100) {
        $error .= "12th marks must be between 0 and 100.<br>";
    }
    
    // Validate 10th marks
    if ($marks_10th < 0 || $marks_10th > 100) {
        $error .= "10th marks must be between 0 and 100.<br>";
    }
    
    // Handle resume upload
    $resume_path = $student['resume_path']; // Keep existing resume path by default
    
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $allowed_ext = ['pdf', 'doc', 'docx'];
        $file_name = $_FILES['resume']['name'];
        $file_size = $_FILES['resume']['size'];
        $file_tmp = $_FILES['resume']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            $error .= "Resume must be in PDF, DOC, or DOCX format.<br>";
        } elseif ($file_size > 2097152) { // 2MB max
            $error .= "Resume file size must be less than 2MB.<br>";
        } else {
            $new_file_name = "resume_" . $_SESSION['student_registration'] . "_" . time() . "." . $file_ext;
            $upload_path = "../uploads/resumes/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            $resume_path = $upload_path . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $resume_path)) {
                // Resume uploaded successfully
            } else {
                $error .= "Failed to upload resume. Please try again.<br>";
            }
        }
    }
    
    // If no errors, update profile
    if (empty($error)) {
        // Debug the branch value and print data types        // Clean and validate branch data
        $branch = trim($_POST['branch']);
        if (empty($branch)) {
            $error .= "Branch cannot be empty.<br>";
        }
          $update_query = "UPDATE students SET
                        dob = ?, 
                        age = ?, 
                        hometown = ?, 
                        college = ?, 
                        degree = ?, 
                        branch = ?, 
                        semester = ?, 
                        cgpa = ?, 
                        marks_12th = ?, 
                        marks_10th = ?, 
                        resume_path = ? 
                        WHERE id = ?";
        
        // Debug the SQL and values before execution
        error_log("Branch value: '" . $branch . "', type: " . gettype($branch));
        
        $stmt = $conn->prepare($update_query);
        // Fix: Use proper type bindings - ensuring branch is treated as a string
        $stmt->bind_param("sisssssiddss", $dob, $age, $hometown, $college, $degree, $branch, $semester, $cgpa, $marks_12th, $marks_10th, $resume_path, $student_id);
          $success = $stmt->execute();
        error_log("SQL execution result: " . ($success ? "Success" : "Failed with error: " . $stmt->error));
        
        if ($success) {
            // Profile updated successfully, redirect to dashboard
            error_log("Redirecting to dashboard.php");
            // Update session variable if needed
            $_SESSION['student_name'] = $student['name'] ?? $_SESSION['student_name'];
            
            // Make sure to end any output buffering
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Error updating profile: " . $stmt->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Profile - VIT Placement Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">VIT Placement Portal</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">My Profile</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?></span>
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
                        <h4 class="mb-0">Complete Your Profile</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Welcome!</strong> Please complete your profile to continue. This information will be used for all job applications.
                        </div>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="complete_profile.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dob">Date of Birth</label>
                                        <input type="date" class="form-control" id="dob" name="dob" value="<?php echo isset($student['dob']) ? $student['dob'] : ''; ?>" required>
                                        <div class="invalid-feedback">
                                            Please provide your date of birth.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="age">Age</label>
                                        <input type="number" class="form-control" id="age" name="age" value="<?php echo isset($student['age']) ? $student['age'] : ''; ?>" required min="16" max="50">
                                        <div class="invalid-feedback">
                                            Please provide a valid age.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="hometown">Hometown</label>
                                <input type="text" class="form-control" id="hometown" name="hometown" value="<?php echo isset($student['hometown']) ? htmlspecialchars($student['hometown']) : ''; ?>" required>
                                <div class="invalid-feedback">
                                    Please provide your hometown.
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="college">College</label>
                                <input type="text" class="form-control" id="college" name="college" value="Vellore Institute of Technology" readonly required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="degree">Degree</label>
                                        <select class="form-control" id="degree" name="degree" required>
                                            <option value="">Select Degree</option>
                                            <option value="B.Tech" <?php echo (isset($student['degree']) && $student['degree'] == 'B.Tech') ? 'selected' : ''; ?>>B.Tech</option>
                                            <option value="M.Tech" <?php echo (isset($student['degree']) && $student['degree'] == 'M.Tech') ? 'selected' : ''; ?>>M.Tech</option>
                                            <option value="BCA" <?php echo (isset($student['degree']) && $student['degree'] == 'BCA') ? 'selected' : ''; ?>>BCA</option>
                                            <option value="MCA" <?php echo (isset($student['degree']) && $student['degree'] == 'MCA') ? 'selected' : ''; ?>>MCA</option>
                                            <option value="BSc" <?php echo (isset($student['degree']) && $student['degree'] == 'BSc') ? 'selected' : ''; ?>>BSc</option>
                                            <option value="MSc" <?php echo (isset($student['degree']) && $student['degree'] == 'MSc') ? 'selected' : ''; ?>>MSc</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select your degree.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">                                        <label for="branch">Branch</label>                                        <input type="text" class="form-control" id="branch" name="branch" placeholder="e.g., Computer Science, Electronics" value="<?php echo !empty($student['branch']) ? htmlspecialchars($student['branch']) : ''; ?>" required>
                                        <div class="invalid-feedback">
                                            Please provide your branch.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="semester">Current Semester</label>
                                        <select class="form-control" id="semester" name="semester" required>
                                            <option value="">Select Semester</option>
                                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo (isset($student['semester']) && $student['semester'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select your current semester.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cgpa">CGPA</label>
                                        <input type="number" step="0.01" class="form-control" id="cgpa" name="cgpa" placeholder="Enter your CGPA (0-10)" value="<?php echo isset($student['cgpa']) ? $student['cgpa'] : ''; ?>" required min="0" max="10">
                                        <div class="invalid-feedback">
                                            Please provide a valid CGPA (between 0 and 10).
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="marks_12th">12th Standard Marks (%)</label>
                                        <input type="number" step="0.01" class="form-control" id="marks_12th" name="marks_12th" placeholder="Enter your 12th standard marks" value="<?php echo isset($student['marks_12th']) ? $student['marks_12th'] : ''; ?>" required min="0" max="100">
                                        <div class="invalid-feedback">
                                            Please provide valid 12th standard marks.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="marks_10th">10th Standard Marks (%)</label>
                                        <input type="number" step="0.01" class="form-control" id="marks_10th" name="marks_10th" placeholder="Enter your 10th standard marks" value="<?php echo isset($student['marks_10th']) ? $student['marks_10th'] : ''; ?>" required min="0" max="100">
                                        <div class="invalid-feedback">
                                            Please provide valid 10th standard marks.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="resume">Resume (PDF, DOC, or DOCX format, max 2MB)</label>
                                <input type="file" class="form-control-file" id="resume" name="resume" accept=".pdf,.doc,.docx" <?php echo empty($student['resume_path']) ? 'required' : ''; ?>>
                                <?php if (!empty($student['resume_path'])): ?>
                                    <small class="text-success">You have already uploaded a resume. Upload a new one to replace it.</small>
                                <?php endif; ?>
                                <div class="invalid-feedback">
                                    Please upload your resume.
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Save Profile</button>
                        </form>
                    </div>
                </div>
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
            
            // Calculate age automatically based on DOB
            var dobInput = document.getElementById('dob');
            var ageInput = document.getElementById('age');
            
            dobInput.addEventListener('change', function() {
                if (dobInput.value) {
                    var dob = new Date(dobInput.value);
                    var today = new Date();
                    var age = today.getFullYear() - dob.getFullYear();
                    var monthDiff = today.getMonth() - dob.getMonth();
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                        age--;
                    }
                    ageInput.value = age;
                }
            });
        }, false);
    })();
    </script>
</body>
</html>
