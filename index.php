<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIT Placement Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">placeVIT</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student/login.php">Student Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cdc/login.php">CDC Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="jumbotron bg-light-blue">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h1 class="display-4">VIT Placement Portal</h1>
                    <p class="lead">Simplifying the internship and job application process for students and streamlining placement management for CDC staff.</p>
                    <hr class="my-4">
                    <p>Create your profile once and apply to multiple opportunities with ease.</p>
                    <div class="mt-4">
                        <a class="btn btn-primary btn-lg mr-2" href="student/register.php" role="button">Student Registration</a>
                        <a class="btn btn-accent btn-lg" href="cdc/register.php" role="button">CDC Registration</a>
                    </div>
                </div>
                <div class="col-md-6 d-flex justify-content-center align-items-center">
                    <img src="assets\job-interview-conversation_74855-7566.avif" alt="Placement Illustration" class="img-fluid" style="max-height: 300px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Features</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-user-graduate fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title text-center">For Students</h5>
                            <ul class="card-text">
                                <li>Create a comprehensive profile</li>
                                <li>Apply to jobs with one click</li>
                                <li>Track application status</li>
                                <li>Update profile anytime</li>
                                <li>Get real-time notifications</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-building fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title text-center">For CDC</h5>
                            <ul class="card-text">
                                <li>Post job opportunities easily</li>
                                <li>Set application deadlines</li>
                                <li>Review applicants in a structured format</li>
                                <li>Update application statuses</li>
                                <li>Generate reports and analytics</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-handshake fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title text-center">Benefits</h5>
                            <ul class="card-text">
                                <li>Simplified application process</li>
                                <li>Efficient management system</li>
                                <li>Reduced paperwork</li>
                                <li>Better communication</li>
                                <li>Enhanced placement tracking</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
</body>
</html>
