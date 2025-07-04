<?php
require_once '../includes/config.php';

// Check if student is logged in
if (!isset($_SESSION['student_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}
$student_id = $_SESSION['student_id'];
$query = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Check if the form is submitted for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $hometown = mysqli_real_escape_string($conn, $_POST['hometown']);
    $degree = mysqli_real_escape_string($conn, $_POST['degree']);
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $semester = isset($_POST['semester']) ? intval($_POST['semester']) : null;
    $cgpa = isset($_POST['cgpa']) ? floatval($_POST['cgpa']) : null;
    $marks_12th = isset($_POST['marks_12th']) ? floatval($_POST['marks_12th']) : null;
    $marks_10th = isset($_POST['marks_10th']) ? floatval($_POST['marks_10th']) : null;
    $error = "";
    
    if ($cgpa < 0 || $cgpa > 10) {
        $error .= "CGPA must be between 0 and 10.<br>";
    }
    
    if ($marks_12th < 0 || $marks_12th > 100) {
        $error .= "12th marks must be between 0 and 100.<br>";
    }
    
    if ($marks_10th < 0 || $marks_10th > 100) {
        $error .= "10th marks must be between 0 and 100.<br>";
    }

    $resume_path = $student['resume_path']; 
    
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $allowed_ext = ['pdf', 'doc', 'docx'];
        $file_name = $_FILES['resume']['name'];
        $file_size = $_FILES['resume']['size'];
        $file_tmp = $_FILES['resume']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            $error .= "Resume must be in PDF, DOC, or DOCX format.<br>";
        } elseif ($file_size > 2097152) { 
            $error .= "Resume file size must be less than 2MB.<br>";
        } else {
            $new_file_name = "resume_" . $_SESSION['student_registration'] . "_" . time() . "." . $file_ext;
            $upload_path = "../uploads/resumes/";
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            $resume_path = $upload_path . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $resume_path)) {
            } else {
                $error .= "Failed to upload resume. Please try again.<br>";
            }
        }
    }
      // If no errors, update profile
    if (empty($error)) {
        $branch = trim($_POST['branch']);
        if (empty($branch)) {
            $error .= "Branch cannot be empty.<br>";
        }
        $update_query = "UPDATE students SET 
                        name = ?,
                        dob = ?, 
                        age = ?, 
                        hometown = ?, 
                        degree = ?, 
                        branch = ?, 
                        semester = ?, 
                        cgpa = ?, 
                        marks_12th = ?, 
                        marks_10th = ?, 
                        resume_path = ? 
                        WHERE id = ?";
          
        error_log("Profile - Branch value: '" . $branch . "', type: " . gettype($branch));
            $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssissssdddss", $name, $dob, $age, $hometown, $degree, $branch, $semester, $cgpa, $marks_12th, $marks_10th, $resume_path, $student_id);
        
        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            $_SESSION['student_name'] = $name;
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
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
    <title>My Profile - VIT Placement Portal</title>
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
                        <a class="nav-link" href="profile.php">My Profile</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars($student['name']); ?></span>
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
                <div class="profile-header mb-4">
                    <h2>My Profile</h2>
                    <p class="lead">
                        <i class="fas fa-info-circle"></i> This information will be sent with all your job applications.
                    </p>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="profile.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="registration_number">Registration Number</label>
                                        <input type="text" class="form-control" id="registration_number" value="<?php echo htmlspecialchars($student['registration_number']); ?>" readonly>
                                        <small class="text-muted">Registration number cannot be changed.</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                                        <div class="invalid-feedback">
                                            Please provide your full name.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>
                                        <small class="text-muted">Email address cannot be changed.</small>
                                    </div>
                                </div>
                            </div>
                            
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
                                <input type="text" class="form-control" id="college" value="Vellore Institute of Technology" readonly>
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
                                    <div class="form-group">
                                        <label for="branch">Branch</label>
                                        <input type="text" class="form-control" id="branch" name="branch" value="<?php echo isset($student['branch']) ? htmlspecialchars($student['branch']) : ''; ?>" required>
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
                                        <input type="number" step="0.01" class="form-control" id="cgpa" name="cgpa" value="<?php echo isset($student['cgpa']) ? $student['cgpa'] : ''; ?>" required min="0" max="10">
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
                                        <input type="number" step="0.01" class="form-control" id="marks_12th" name="marks_12th" value="<?php echo isset($student['marks_12th']) ? $student['marks_12th'] : ''; ?>" required min="0" max="100">
                                        <div class="invalid-feedback">
                                            Please provide valid 12th standard marks.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="marks_10th">10th Standard Marks (%)</label>
                                        <input type="number" step="0.01" class="form-control" id="marks_10th" name="marks_10th" value="<?php echo isset($student['marks_10th']) ? $student['marks_10th'] : ''; ?>" required min="0" max="100">
                                        <div class="invalid-feedback">
                                            Please provide valid 10th standard marks.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="resume">Resume (PDF, DOC, or DOCX format, max 2MB)</label>
                                <input type="file" class="form-control-file" id="resume" name="resume" accept=".pdf,.doc,.docx">
                                <?php if (!empty($student['resume_path'])): ?>
                                    <div class="mt-2">
                                        <a href="<?php echo $student['resume_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-alt"></i> View Current Resume
                                        </a>
                                        <small class="text-muted ml-2">Upload a new resume to replace the current one.</small>
                                    </div>
                                <?php else: ?>
                                    <div class="text-danger mt-2">
                                        <i class="fas fa-exclamation-triangle"></i> You haven't uploaded a resume yet. This is required for job applications.
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </div>
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
