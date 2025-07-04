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

// Check if profile is complete
if (empty($student['dob']) || empty($student['college']) || 
    empty($student['degree']) || empty($student['branch']) || 
    $student['branch'] === '0' || empty($student['cgpa'])) {
    header("Location: complete_profile.php");
    exit();
}

// Handle job application
if (isset($_POST['apply_job']) && isset($_POST['job_id'])) {
    $job_id = intval($_POST['job_id']);
    $check_query = "SELECT * FROM applications WHERE student_id = ? AND job_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $student_id, $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $application_error = "You have already applied for this job.";
    } else {
        $apply_query = "INSERT INTO applications (student_id, job_id, status) VALUES (?, ?, 'applied')";
        $stmt = $conn->prepare($apply_query);
        $stmt->bind_param("ii", $student_id, $job_id);
        
        if ($stmt->execute()) {
            $application_success = "Application submitted successfully!";
        } else {
            $application_error = "Error submitting application: " . $stmt->error;
        }
    }
}

// Get all job opportunities that are still open (deadline > current date)
$jobs_query = "SELECT * FROM job_opportunities WHERE deadline >= NOW() ORDER BY created_at DESC";
$jobs_result = $conn->query($jobs_query);

$applied_jobs_query = "SELECT a.*, j.company_name, j.role, j.ctc, j.deadline
                      FROM applications a 
                      INNER JOIN job_opportunities j ON a.job_id = j.id
                      WHERE a.student_id = ?
                      ORDER BY a.applied_at DESC";
$stmt = $conn->prepare($applied_jobs_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$applied_jobs_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - VIT Placement Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">VIT Placement Portal</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
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

    <div class="container dashboard-container py-5">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="jumbotron bg-light-blue py-4">
                    <h2>Welcome, <?php echo htmlspecialchars($student['name']); ?>!</h2>
                    <p>Reg. No: <?php echo htmlspecialchars($student['registration_number']); ?> | CGPA: <?php echo $student['cgpa']; ?> | <?php echo htmlspecialchars($student['degree']); ?> - <?php echo htmlspecialchars($student['branch']); ?></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php if (isset($application_success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $application_success; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($application_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $application_error; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">My Applications</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($applied_jobs_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th>Role</th>
                                            <th>CTC</th>
                                            <th>Applied On</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($application = $applied_jobs_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($application['company_name']); ?></td>
                                                <td><?php echo htmlspecialchars($application['role']); ?></td>
                                                <td><?php echo htmlspecialchars($application['ctc']); ?></td>
                                                <td><?php echo date("d M Y, h:i A", strtotime($application['applied_at'])); ?></td>
                                                <td>
                                                    <?php 
                                                    switch ($application['status']) {
                                                        case 'applied':
                                                            echo '<span class="badge badge-applied">Applied</span>';
                                                            break;
                                                        case 'shortlisted':
                                                            echo '<span class="badge badge-shortlisted">Shortlisted</span>';
                                                            break;
                                                        case 'selected':
                                                            echo '<span class="badge badge-selected">Selected</span>';
                                                            break;
                                                        case 'rejected':
                                                            echo '<span class="badge badge-rejected">Rejected</span>';
                                                            break;
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center">You haven't applied to any jobs yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Available Job Opportunities</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($jobs_result->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($job = $jobs_result->fetch_assoc()): 
                                    $check_query = "SELECT * FROM applications WHERE student_id = ? AND job_id = ?";
                                    $stmt = $conn->prepare($check_query);
                                    $stmt->bind_param("ii", $student_id, $job['id']);
                                    $stmt->execute();
                                    $check_result = $stmt->get_result();
                                    $already_applied = $check_result->num_rows > 0;
                                ?>
                                    <div class="col-lg-6 mb-4">
                                        <div class="card job-card h-100">
                                            <div class="card-body">
                                                <h5 class="job-company"><?php echo htmlspecialchars($job['company_name']); ?></h5>
                                                <h6 class="job-role"><?php echo htmlspecialchars($job['role']); ?></h6>
                                                <p><strong>CTC:</strong> <?php echo htmlspecialchars($job['ctc']); ?></p>
                                                <p class="job-deadline"><i class="fas fa-clock"></i> Deadline: <?php echo date("d M Y, h:i A", strtotime($job['deadline'])); ?></p>
                                                
                                                <?php if ($job['job_description_path']): ?>
                                                    <a href="<?php echo htmlspecialchars($job['job_description_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary mb-2">
                                                        <i class="fas fa-file-alt"></i> View Job Description
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <form method="post" action="dashboard.php">
                                                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                                    <?php if ($already_applied): ?>
                                                        <button type="button" class="btn btn-success btn-block" disabled>
                                                            <i class="fas fa-check"></i> Already Applied
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" name="apply_job" class="btn btn-primary btn-block">
                                                            <i class="fas fa-paper-plane"></i> Apply Now
                                                        </button>
                                                    <?php endif; ?>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center">No job opportunities available at the moment. Please check back later.</p>
                        <?php endif; ?>
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
</body>
</html>
