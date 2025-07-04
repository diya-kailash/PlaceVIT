<?php
require_once '../includes/config.php';

// Check if CDC staff is logged in
if (!isset($_SESSION['cdc_id']) || $_SESSION['user_type'] !== 'cdc') {
    header("Location: login.php");
    exit();
}

// Get CDC staff data
$cdc_id = $_SESSION['cdc_id'];

// Count total job opportunities
$jobs_count_query = "SELECT COUNT(*) as total_jobs FROM job_opportunities WHERE posted_by = ?";
$stmt = $conn->prepare($jobs_count_query);
$stmt->bind_param("i", $cdc_id);
$stmt->execute();
$jobs_count_result = $stmt->get_result()->fetch_assoc();
$total_jobs = $jobs_count_result['total_jobs'];

// Count total applications
$applications_count_query = "SELECT COUNT(*) as total_applications 
                            FROM applications a 
                            JOIN job_opportunities j ON a.job_id = j.id 
                            WHERE j.posted_by = ?";
$stmt = $conn->prepare($applications_count_query);
$stmt->bind_param("i", $cdc_id);
$stmt->execute();
$applications_count_result = $stmt->get_result()->fetch_assoc();
$total_applications = $applications_count_result['total_applications'];

// Count applications by status
$status_counts = [];
$statuses = ['applied', 'shortlisted', 'selected', 'rejected'];

foreach ($statuses as $status) {
    $status_query = "SELECT COUNT(*) as count 
                    FROM applications a 
                    JOIN job_opportunities j ON a.job_id = j.id 
                    WHERE j.posted_by = ? AND a.status = ?";
    $stmt = $conn->prepare($status_query);
    $stmt->bind_param("is", $cdc_id, $status);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $status_counts[$status] = $result['count'];
}

// Get recent job opportunities
$recent_jobs_query = "SELECT * FROM job_opportunities WHERE posted_by = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($recent_jobs_query);
$stmt->bind_param("i", $cdc_id);
$stmt->execute();
$recent_jobs_result = $stmt->get_result();

// Get recent applications
$recent_applications_query = "SELECT a.*, s.name as student_name, s.registration_number, j.company_name, j.role
                             FROM applications a 
                             JOIN students s ON a.student_id = s.id
                             JOIN job_opportunities j ON a.job_id = j.id
                             WHERE j.posted_by = ?
                             ORDER BY a.applied_at DESC LIMIT 10";
$stmt = $conn->prepare($recent_applications_query);
$stmt->bind_param("i", $cdc_id);
$stmt->execute();
$recent_applications_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDC Dashboard - VIT Placement Portal</title>
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
                    <li class="nav-item active">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
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

    <div class="container dashboard-container py-5">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="jumbotron bg-light-blue py-4">
                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['cdc_name']); ?>!</h2>
                    <p>Designation: <?php echo htmlspecialchars($_SESSION['cdc_designation']); ?> | Email: <?php echo htmlspecialchars($_SESSION['cdc_email']); ?></p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-briefcase fa-2x text-primary mb-3"></i>
                        <h5>Total Jobs Posted</h5>
                        <p class="dashboard-stats"><?php echo $total_jobs; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-file-alt fa-2x text-primary mb-3"></i>
                        <h5>Total Applications</h5>
                        <p class="dashboard-stats"><?php echo $total_applications; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-user-check fa-2x text-primary mb-3"></i>
                        <h5>Students Shortlisted</h5>
                        <p class="dashboard-stats"><?php echo $status_counts['shortlisted']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-trophy fa-2x text-primary mb-3"></i>
                        <h5>Students Selected</h5>
                        <p class="dashboard-stats"><?php echo $status_counts['selected']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Job Opportunities</h5>
                        <a href="job_opportunities.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_jobs_result->num_rows > 0): ?>
                            <div class="list-group">
                                <?php while ($job = $recent_jobs_result->fetch_assoc()): ?>
                                    <a href="view_job.php?id=<?php echo $job['id']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                                            <small><?php echo date("d M Y", strtotime($job['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($job['role']); ?> - <?php echo htmlspecialchars($job['ctc']); ?></p>
                                        <small>Deadline: <?php echo date("d M Y, h:i A", strtotime($job['deadline'])); ?></small>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center">No job opportunities posted yet.</p>
                        <?php endif; ?>
                        <div class="text-center mt-3">
                            <a href="post_job.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Post New Job Opportunity
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Applications</h5>
                        <a href="applications.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_applications_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Job</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($application = $recent_applications_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <small><?php echo htmlspecialchars($application['student_name']); ?></small><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($application['registration_number']); ?></small>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($application['company_name']); ?></small><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($application['role']); ?></small>
                                                </td>
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
                                                <td><small><?php echo date("d M Y", strtotime($application['applied_at'])); ?></small></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center">No applications received yet.</p>
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
