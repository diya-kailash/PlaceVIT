<?php
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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
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
    <div class="jumbotron bg-light-blue">
        <div class="container">
            <div class="row align-items-center" style="min-height: 500px; padding: 40px 0;">
                <div class="col-md-6">
                    <h1 class="display-4" style="font-size: 3.2rem; font-weight: 700;">VIT Placement Portal</h1>
                    <p class="lead" style="font-size: 1.3rem; margin: 25px 0;">Simplifying the internship and job application process for students and streamlining placement management for CDC staff.</p>
                    <hr class="my-4" style="border-top: 2px solid var(--primary-color); width: 80px; margin-left: 0;">
                    <p style="font-size: 1.1rem; margin-bottom: 30px;">Create your profile once and apply to multiple opportunities with ease.</p>
                    <div class="mt-5">
                        <a class="btn btn-primary btn-lg mr-3 px-4 py-2" href="student/register.php" role="button">Student Registration</a>
                        <a class="btn btn-accent btn-lg px-4 py-2" href="cdc/register.php" role="button">CDC Registration</a>
                    </div>
                </div>
                <div class="col-md-6 d-flex justify-content-center align-items-center">
                    <img src="assets\job-interview-conversation_74855-7566.avif" alt="Placement Illustration" class="img-fluid shadow-lg" style="max-height: 380px; border-radius: 10px;">
                </div>
            </div>
        </div>
    </div>   
    <section>
        <div class="container">
            <h2 class="text-center">Key Features</h2>
            <div class="row mt-5">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <i class="fas fa-user-graduate fa-4x text-primary mb-4"></i>
                            </div>
                            <h5 class="card-title text-center mb-4 font-weight-bold">For Students</h5>
                            <ul class="card-text pl-3" style="line-height: 2">
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
                    <div class="card h-100 border-0">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <i class="fas fa-building fa-4x text-primary mb-4"></i>
                            </div>
                            <h5 class="card-title text-center mb-4 font-weight-bold">For CDC</h5>
                            <ul class="card-text pl-3" style="line-height: 2">
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
                    <div class="card h-100 border-0">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <i class="fas fa-handshake fa-4x text-primary mb-4"></i>
                            </div>
                            <h5 class="card-title text-center mb-4 font-weight-bold">Benefits</h5>
                            <ul class="card-text pl-3" style="line-height: 2">
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

    <footer class="footer">
        <div class="container text-center">
            <p>&copy; <?php echo date("Y"); ?> VIT Placement Portal. All Rights Reserved.</p>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
