<?php
require_once '../includes/config.php';

// Check if CDC staff is logged in
if (!isset($_SESSION['cdc_id']) || $_SESSION['user_type'] !== 'cdc') {
    header("Location: login.php");
    exit();
}

$cdc_id = $_SESSION['cdc_id'];

// Delete job opportunity if requested
if (isset($_POST['delete_job']) && isset($_POST['job_id'])) {
    $job_id = intval($_POST['job_id']);
    
    // Check if the job belongs to the current CDC staff
    $check_query = "SELECT * FROM job_opportunities WHERE id = ? AND posted_by = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $job_id, $cdc_id);
    $stmt->execute();
    $result = $stmt->get_result();
      if ($result->num_rows > 0) {
        // Get job details to delete related file
        $job = $result->fetch_assoc();
        $jd_path = $job['job_description_path'];
        
        // First delete all applications associated with this job
        $delete_applications_query = "DELETE FROM applications WHERE job_id = ?";
        $stmt = $conn->prepare($delete_applications_query);
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        
        // Then delete job from database
        $delete_query = "DELETE FROM job_opportunities WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $job_id);
        
        if ($stmt->execute()) {
            // Delete related file if exists
            if (!empty($jd_path) && file_exists($jd_path)) {
                unlink($jd_path);
            }
            
            $success = "Job opportunity deleted successfully.";
        } else {
            $error = "Error deleting job opportunity.";
        }
    } else {
        $error = "You don't have permission to delete this job opportunity.";
    }
}

// Get all job opportunities posted by this CDC staff
$jobs_query = "SELECT * FROM job_opportunities WHERE posted_by = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($jobs_query);
$stmt->bind_param("i", $cdc_id);
$stmt->execute();
$jobs_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Opportunities - VIT Placement Portal</title>
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
            <div class="col-md-6">
                <h2>Job Opportunities</h2>
            </div>
            <div class="col-md-6 text-right">
                <a href="post_job.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Post New Job
                </a>
            </div>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <?php if ($jobs_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Role</th>
                                    <th>CTC</th>
                                    <th>Deadline</th>
                                    <th>Applications</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($job = $jobs_result->fetch_assoc()): 
                                    // Count applications for this job
                                    $app_query = "SELECT COUNT(*) as app_count FROM applications WHERE job_id = ?";
                                    $stmt = $conn->prepare($app_query);
                                    $stmt->bind_param("i", $job['id']);
                                    $stmt->execute();
                                    $app_result = $stmt->get_result()->fetch_assoc();
                                    $app_count = $app_result['app_count'];
                                    
                                    // Determine job status
                                    $now = new DateTime();
                                    $deadline = new DateTime($job['deadline']);
                                    $status = ($now > $deadline) ? 'closed' : 'open';
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($job['company_name']); ?></td>
                                        <td><?php echo htmlspecialchars($job['role']); ?></td>
                                        <td><?php echo htmlspecialchars($job['ctc']); ?></td>
                                        <td><?php echo date("d M Y, h:i A", strtotime($job['deadline'])); ?></td>
                                        <td>
                                            <a href="view_applications.php?job_id=<?php echo $job['id']; ?>" class="badge badge-primary">
                                                <?php echo $app_count; ?> Applications
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($status == 'open'): ?>
                                                <span class="badge badge-success">Open</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Closed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="view_job.php?id=<?php echo $job['id']; ?>" class="btn btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo $job['id']; ?>" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p>No job opportunities posted yet.</p>
                        <a href="post_job.php" class="btn btn-primary">Post Your First Job</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->    <!-- Delete Confirmation Modals -->
    <?php 
    // Reset the results pointer to beginning
    $jobs_result->data_seek(0);
    while ($job = $jobs_result->fetch_assoc()): 
    ?>
    <div class="modal fade" id="deleteModal<?php echo $job['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $job['id']; ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel<?php echo $job['id']; ?>">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the job opportunity for <?php echo htmlspecialchars($job['company_name']); ?> - <?php echo htmlspecialchars($job['role']); ?>?</p>
                    <p class="text-danger">This action cannot be undone and will also delete all associated applications.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form method="post" action="job_opportunities.php">
                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                        <button type="submit" name="delete_job" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>

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