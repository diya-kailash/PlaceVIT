<?php
require_once '../includes/config.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $designation = mysqli_real_escape_string($conn, $_POST['designation']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $error = "";

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@vit\.ac\.in$/', $email)) {
        $error .= "Invalid email format. Please use your VIT email address.<br>";
    }

    // Check if passwords match
    if ($password != $confirm_password) {
        $error .= "Passwords do not match.<br>";
    }

    // Validate password strength
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error .= "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.<br>";
    }

    // Check if email already exists
    $check_query = "SELECT * FROM cdc_staff WHERE email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error .= "Email already exists.<br>";
    }

    // If no errors, proceed with registration
    if (empty($error)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user data into database
        $insert_query = "INSERT INTO cdc_staff (name, email, password, designation, department) 
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssss", $name, $email, $hashed_password, $designation, $department);
        
        if ($stmt->execute()) {
            $_SESSION['registration_success'] = true;
            header("Location: login.php");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDC Staff Registration - VIT Placement Portal</title>
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
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../student/login.php">Student Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">CDC Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="auth-wrapper">
        <div class="auth-card card">
            <div class="card-header">
                <h4 class="text-center">CDC Staff Registration</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="register.php" class="needs-validation" novalidate>
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        <div class="invalid-feedback">
                            Please provide your full name.
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your VIT email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required pattern="^[a-zA-Z0-9._%+-]+@vit\.ac\.in$">
                        <small class="form-text text-muted">Please use your VIT email address (@vit.ac.in)</small>
                        <div class="invalid-feedback">
                            Please provide a valid VIT email address.
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="designation">Designation</label>
                                <input type="text" class="form-control" id="designation" name="designation" placeholder="Enter your designation" value="<?php echo isset($_POST['designation']) ? htmlspecialchars($_POST['designation']) : ''; ?>" required>
                                <div class="invalid-feedback">
                                    Please provide your designation.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" class="form-control" id="department" name="department" placeholder="Enter your department" value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>" required>
                                <div class="invalid-feedback">
                                    Please provide your department.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$">
                        <small class="form-text text-muted">Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character</small>
                        <div class="invalid-feedback">
                            Please create a strong password.
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        <div class="invalid-feedback">
                            Please confirm your password.
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>
                
                <div class="mt-3 text-center">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
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
            
            // Check if password and confirm password match
            var password = document.getElementById("password");
            var confirm_password = document.getElementById("confirm_password");
            
            function validatePassword(){
                if(password.value != confirm_password.value) {
                    confirm_password.setCustomValidity("Passwords don't match");
                } else {
                    confirm_password.setCustomValidity('');
                }
            }
            
            password.onchange = validatePassword;
            confirm_password.onkeyup = validatePassword;
        }, false);
    })();
    </script>
</body>
</html>
