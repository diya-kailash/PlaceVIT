<?php
require_once '../includes/config.php';

// Check if CDC staff is logged in
if (!isset($_SESSION['cdc_id']) || $_SESSION['user_type'] !== 'cdc') {
    header("Location: login.php");
    exit();
}

$cdc_id = $_SESSION['cdc_id'];

// Handle bulk status update
if (isset($_POST['update_status']) && isset($_POST['application_ids']) && isset($_POST['new_status'])) {
    $application_ids = $_POST['application_ids'];
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    
    if (!empty($application_ids)) {
        // Convert array of IDs to comma-separated string for the query
        $ids_string = implode(',', array_map('intval', $application_ids));
        
        // Update only applications for jobs posted by this CDC staff
        $update_query = "UPDATE applications SET status = ? 
                        WHERE id IN ($ids_string) 
                        AND job_id IN (SELECT id FROM job_opportunities WHERE posted_by = ?)";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_status, $cdc_id);
        
        if ($stmt->execute()) {
            $success = count($application_ids) . " application(s) status updated to " . ucfirst($new_status) . ".";
        } else {
            $error = "Error updating application status: " . $stmt->error;
        }
    } else {
        $error = "No applications selected.";
    }
}

// Get filter parameters
$job_filter = isset($_GET['job_id']) ? intval($_GET['job_id']) : null;
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : null;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : null;

// Build the query based on filters
$query = "SELECT a.*, s.name as student_name, s.registration_number, s.email as student_email, 
          s.cgpa, j.company_name, j.role
          FROM applications a 
          JOIN students s ON a.student_id = s.id
          JOIN job_opportunities j ON a.job_id = j.id
          WHERE j.posted_by = ?";

$params = array($cdc_id);
$types = "i";

// Add filters if provided
if ($job_filter) {
    $query .= " AND j.id = ?";
    $params[] = $job_filter;
    $types .= "i";
}

if ($status_filter) {
    $query .= " AND a.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($search) {
    $query .= " AND (s.name LIKE ? OR s.registration_number LIKE ? OR s.email LIKE ? OR j.company_name LIKE ? OR j.role LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sssss";
}

// Order by
$query .= " ORDER BY a.applied_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$applications_result = $stmt->get_result();

// Get all jobs for filter dropdown
$jobs_query = "SELECT id, company_name, role FROM job_opportunities WHERE posted_by = ? ORDER BY company_name ASC";
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
    <title>Applications - VIT Placement Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
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
                    <li class="nav-item">
                        <a class="nav-link" href="job_opportunities.php">Job Opportunities</a>
                    </li>
                    <li class="nav-item active">
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
                <h2>Student Applications</h2>
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
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" action="applications.php" class="row">
                    <div class="col-md-3 mb-3">
                        <label for="job_id">Filter by Job</label>
                        <select class="form-control" id="job_id" name="job_id">
                            <option value="">All Jobs</option>
                            <?php while ($job = $jobs_result->fetch_assoc()): ?>
                                <option value="<?php echo $job['id']; ?>" <?php echo ($job_filter == $job['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($job['company_name']); ?> - <?php echo htmlspecialchars($job['role']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="status">Filter by Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="applied" <?php echo ($status_filter == 'applied') ? 'selected' : ''; ?>>Applied</option>
                            <option value="shortlisted" <?php echo ($status_filter == 'shortlisted') ? 'selected' : ''; ?>>Shortlisted</option>
                            <option value="selected" <?php echo ($status_filter == 'selected') ? 'selected' : ''; ?>>Selected</option>
                            <option value="rejected" <?php echo ($status_filter == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" placeholder="Search by name, reg. no, email, etc." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <?php if ($job_filter || $status_filter || $search): ?>
                            <a href="applications.php" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form method="post" action="applications.php" id="applicationsForm">
                    <?php if ($applications_result->num_rows > 0): ?>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label" for="selectAll">Select All</label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-right">
                                    <div class="form-inline justify-content-end">
                                        <select class="form-control form-control-sm mr-2" name="new_status">
                                            <option value="">Change Status To</option>
                                            <option value="applied">Applied</option>
                                            <option value="shortlisted">Shortlisted</option>
                                            <option value="selected">Selected</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update Selected</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="30"></th>
                                        <th>Student</th>
                                        <th>Job</th>
                                        <th>CGPA</th>
                                        <th>Applied On</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($app = $applications_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input application-checkbox" type="checkbox" name="application_ids[]" value="<?php echo $app['id']; ?>">
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($app['student_name']); ?></strong>
                                                </div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($app['registration_number']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($app['student_email']); ?></div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($app['company_name']); ?></strong>
                                                </div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($app['role']); ?></div>
                                            </td>
                                            <td><?php echo $app['cgpa']; ?></td>
                                            <td><?php echo date("d M Y, h:i A", strtotime($app['applied_at'])); ?></td>
                                            <td>
                                                <?php 
                                                switch ($app['status']) {
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
                                            <td>
                                                <a href="view_student.php?id=<?php echo $app['student_id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-user"></i> View Profile
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p>No applications found.</p>
                        </div>
                    <?php endif; ?>
                </form>
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
        $(document).ready(function() {
            // Select all checkbox functionality
            $('#selectAll').change(function() {
                $('.application-checkbox').prop('checked', $(this).prop('checked'));
            });
            
            // Update select all checkbox if all individual checkboxes are checked
            $('.application-checkbox').change(function() {
                if ($('.application-checkbox:checked').length == $('.application-checkbox').length) {
                    $('#selectAll').prop('checked', true);
                } else {
                    $('#selectAll').prop('checked', false);
                }
            });
            
            // Form validation for bulk update
            $('#applicationsForm').submit(function(e) {
                if ($('.application-checkbox:checked').length == 0) {
                    e.preventDefault();
                    alert('Please select at least one application.');
                    return false;
                }
                
                if ($('select[name="new_status"]').val() == '') {
                    e.preventDefault();
                    alert('Please select a status to update to.');
                    return false;
                }
            });
        });
    </script>
</body>
</html>
