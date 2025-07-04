<?php
require_once '../includes/config.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registration_number = mysqli_real_escape_string($conn, $_POST['registration_number']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $error = "";    
    if (!preg_match('/^\d{2}[A-Z]{3}\d{4}$/', $registration_number)) {
        $error .= "Invalid registration number format. It should match the VIT format (e.g., 21BIT0001).<br>";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@(vit\.ac\.in|vitstudent\.ac\.in)$/', $email)) {
        $error .= "Invalid email format. Please use your VIT email address.<br>";
    }
    if ($password != $confirm_password) {
        $error .= "Passwords do not match.<br>";
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error .= "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.<br>";
    }

    $check_query = "SELECT * FROM students WHERE registration_number = ? OR email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $registration_number, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error .= "Registration number or email already exists.<br>";
    }

    // If no errors, proceed with registration
    if (empty($error)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO students (registration_number, name, email, password) 
                        VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssss", $registration_number, $name, $email, $hashed_password);
        
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
    <title>Student Registration - VIT Placement Portal</title>
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
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Student Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../cdc/login.php">CDC Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="auth-wrapper">
        <div class="auth-card card">
            <div class="card-header">
                <h4 class="text-center">Student Registration</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="register.php" class="needs-validation" novalidate>
                    <div class="form-group">
                        <label for="registration_number">Registration Number</label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" placeholder="Enter your registration number (e.g., 21BIT0001)" value="<?php echo isset($_POST['registration_number']) ? htmlspecialchars($_POST['registration_number']) : ''; ?>" required pattern="^\d{2}[A-Z]{3}\d{4}$">
                        <small class="form-text text-muted">Your VIT registration number (e.g., 21BIT0001)</small>
                        <div class="invalid-feedback">
                            Please provide a valid registration number.
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        <div class="invalid-feedback">
                            Please provide your full name.
                        </div>
                    </div>
                    
                    <div class="form-group">                        
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your VIT email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required pattern="^[a-zA-Z0-9._%+-]+(@vit\.ac\.in|@vitstudent\.ac\.in)$">
                        <small class="form-text text-muted">Please use your VIT email address (@vit.ac.in or @vitstudent.ac.in)</small>
                        <div class="invalid-feedback">
                            Please provide a valid VIT email address.
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
