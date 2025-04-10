<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if CDC staff is logged in
if (!isset($_SESSION['cdc_id']) || $_SESSION['user_type'] !== 'cdc') {
    header("Location: login.php");
    exit();
}

// Check if job ID is provided
if (!isset($_GET['id'])) {
    header("Location: job_opportunities.php");
    exit();
}

$job_id = intval($_GET['id']);
$cdc_id = $_SESSION['cdc_id'];

// Get job details
$job_query = "SELECT * FROM job_opportunities WHERE id = ? AND posted_by = ?";
$stmt = $conn->prepare($job_query);
$stmt->bind_param("ii", $job_id, $cdc_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: job_opportunities.php");
    exit();
}

$job = $result->fetch_assoc();

// Get job applications
$applications = get_job_applications($conn, $job_id);

// Get application statistics
$stats = get_job_statistics($conn, $job_id);

// Handle bulk status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status']) && isset($_POST['application_ids']) && isset($_POST['new_status'])) {
    $application_ids = $_POST['application_ids'];
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    
    if (!empty($application_ids)) {
        // Convert array of IDs to comma-separated string for the query
        $ids_string = implode(',', array_map('intval', $application_ids));
        
        // Update applications
        $update_query = "UPDATE applications SET status = ? WHERE id IN ($ids_string) AND job_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_status, $job_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = count($application_ids) . " application(s) status updated to " . ucfirst($new_status) . ".";
            header("Location: view_job.php?id=" . $job_id);
            exit();
        } else {
            $_SESSION['error'] = "Error updating application status: " . $stmt->error;
        }
    } else {
        $_SESSION['error'] = "No applications selected.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['company_name']); ?> - <?php echo htmlspecialchars($job['role']); ?> - VIT Placement Portal</title>
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
            <div class="col-md-8">
                <h2><?php echo htmlspecialchars($job['company_name']); ?> - <?php echo htmlspecialchars($job['role']); ?></h2>
            </div>
            <div class="col-md-4 text-right">
                <a href="job_opportunities.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Jobs
                </a>
                <!-- <a href="edit_job.php?id=<?php echo $job_id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Job
                </a> -->
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <table class="table table-bordered">
                            <tr>
                                <th width="20%">Company</th>
                                <td><?php echo htmlspecialchars($job['company_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Role</th>
                                <td><?php echo htmlspecialchars($job['role']); ?></td>
                            </tr>
                            <tr>
                                <th>CTC</th>
                                <td><?php echo htmlspecialchars($job['ctc']); ?></td>
                            </tr>
                            <tr>
                                <th>Deadline</th>
                                <td>
                                    <?php echo format_date($job['deadline'], true); ?>
                                    <span class="ml-2">
                                        <?php 
                                        $now = new DateTime();
                                        $deadline = new DateTime($job['deadline']);
                                        if ($now > $deadline) {
                                            echo '<span class="badge badge-secondary">Closed</span>';
                                        } else {
                                            echo '<span class="badge badge-success">Open</span>';
                                            echo '<small class="text-muted ml-2">' . get_time_remaining($job['deadline']) . '</small>';
                                        }
                                        ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Posted On</th>
                                <td><?php echo format_date($job['created_at'], true); ?></td>
                            </tr>
                            <tr>
                                <th>Job Description</th>
                                <td>
                                    <?php if (!empty($job['job_description_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($job['job_description_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-alt"></i> View Job Description
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No job description file uploaded</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-light-blue">
                                <h5 class="mb-0">Application Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        Total Applications
                                        <span class="badge badge-primary badge-pill"><?php echo $stats['total']; ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        Applied
                                        <span class="badge badge-applied badge-pill"><?php echo $stats['applied']; ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        Shortlisted
                                        <span class="badge badge-shortlisted badge-pill"><?php echo $stats['shortlisted']; ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        Selected
                                        <span class="badge badge-selected badge-pill"><?php echo $stats['selected']; ?></span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        Rejected
                                        <span class="badge badge-rejected badge-pill"><?php echo $stats['rejected']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Applications</h5>
                <div>
                    <a href="#" class="btn btn-sm btn-outline-primary" onclick="exportToCSV()">
                        <i class="fas fa-download"></i> Export to CSV
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="post" action="view_job.php?id=<?php echo $job_id; ?>" id="applicationsForm">
                    <?php if ($applications->num_rows > 0): ?>
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
                            <table class="table table-hover" id="applicationsTable">
                                <thead>
                                    <tr>
                                        <th width="30"></th>
                                        <th>Student</th>
                                        <th>Registration Number</th>
                                        <th>Branch</th>
                                        <th>CGPA</th>
                                        <th>Applied On</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($app = $applications->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input application-checkbox" type="checkbox" name="application_ids[]" value="<?php echo $app['id']; ?>">
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($app['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($app['registration_number']); ?></td>
                                            <td><?php echo htmlspecialchars($app['branch']); ?></td>
                                            <td><?php echo $app['cgpa']; ?></td>
                                            <td><?php echo format_date($app['applied_at'], true); ?></td>
                                            <td><?php echo get_status_badge($app['status']); ?></td>
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
                            <p>No applications received yet for this job.</p>
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
        
        // Function to export table to CSV
        function exportToCSV() {
            var csv = [];
            var rows = document.getElementById("applicationsTable").querySelectorAll("tr");
            
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll("td, th");
                
                for (var j = 1; j < cols.length - 1; j++) { // Skip checkbox column and actions column
                    // Get the text content, removing any HTML elements
                    var cellContent = cols[j].innerText.trim();
                    row.push('"' + cellContent.replace(/"/g, '""') + '"');
                }
                
                csv.push(row.join(","));        
            }
            
            var csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "<?php echo preg_replace('/[^a-zA-Z0-9]/', '_', $job['company_name'] . '_' . $job['role']); ?>_applications.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>
