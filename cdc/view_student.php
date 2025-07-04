<?php
require_once '../includes/config.php';

// Check if CDC staff is logged in
if (!isset($_SESSION['cdc_id']) || $_SESSION['user_type'] !== 'cdc') {
    header("Location: login.php");
    exit();
}


if (!isset($_GET['id'])) {
    header("Location: applications.php");
    exit();
}
$student_id = intval($_GET['id']);

// Get student details
$student_query = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: applications.php");
    exit();
}

$student = $result->fetch_assoc();

// Get student's applications
$applications_query = "SELECT a.*, j.company_name, j.role, j.ctc 
                      FROM applications a 
                      JOIN job_opportunities j ON a.job_id = j.id 
                      WHERE a.student_id = ?
                      ORDER BY a.applied_at DESC";
$stmt = $conn->prepare($applications_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$applications_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - VIT Placement Portal</title>
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

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Student Profile</h2>
            </div>
            <div class="col-md-4 text-right">
                <a href="applications.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Applications
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Name</th>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Registration Number</th>
                                        <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date of Birth</th>
                                        <td><?php echo isset($student['dob']) ? date("d M Y", strtotime($student['dob'])) : 'Not provided'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Age</th>
                                        <td><?php echo isset($student['age']) ? $student['age'] : 'Not provided'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Hometown</th>
                                        <td><?php echo isset($student['hometown']) ? htmlspecialchars($student['hometown']) : 'Not provided'; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="mb-3">
                                    <i class="fas fa-user-graduate fa-5x text-primary"></i>
                                </div>
                                <?php if (isset($student['resume_path']) && !empty($student['resume_path'])): ?>
                                    <a href="<?php echo htmlspecialchars($student['resume_path']); ?>" target="_blank" class="btn btn-outline-primary">
                                        <i class="fas fa-file-alt"></i> View Resume
                                    </a>
                                <?php else: ?>
                                    <p class="text-muted">No resume uploaded</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Academic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">College</th>
                                        <td><?php echo isset($student['college']) ? htmlspecialchars($student['college']) : 'Not provided'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Degree</th>
                                        <td><?php echo isset($student['degree']) ? htmlspecialchars($student['degree']) : 'Not provided'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Branch</th>
                                        <td><?php echo isset($student['branch']) ? htmlspecialchars($student['branch']) : 'Not provided'; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Current Semester</th>
                                        <td><?php echo isset($student['semester']) ? $student['semester'] : 'Not provided'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>CGPA</th>
                                        <td><?php echo isset($student['cgpa']) ? $student['cgpa'] : 'Not provided'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>12th Standard</th>
                                        <td><?php echo isset($student['marks_12th']) ? $student['marks_12th'] . '%' : 'Not provided'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>10th Standard</th>
                                        <td><?php echo isset($student['marks_10th']) ? $student['marks_10th'] . '%' : 'Not provided'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Application History</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($applications_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th>Role</th>
                                            <th>CTC</th>
                                            <th>Applied On</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($app = $applications_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                                                <td><?php echo htmlspecialchars($app['role']); ?></td>
                                                <td><?php echo htmlspecialchars($app['ctc']); ?></td>
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
                                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateStatusModal<?php echo $app['id']; ?>">
                                                        Update Status
                                                    </button>
                                                    
                                                    <!-- Update Status Modal -->
                                                    <div class="modal fade" id="updateStatusModal<?php echo $app['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateStatusLabel<?php echo $app['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="updateStatusLabel<?php echo $app['id']; ?>">Update Application Status</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <form method="post" action="update_application_status.php">
                                                                        <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                                        <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                                                                        <div class="form-group">
                                                                            <label for="status<?php echo $app['id']; ?>">Status</label>
                                                                            <select class="form-control" id="status<?php echo $app['id']; ?>" name="status">
                                                                                <option value="applied" <?php echo ($app['status'] == 'applied') ? 'selected' : ''; ?>>Applied</option>
                                                                                <option value="shortlisted" <?php echo ($app['status'] == 'shortlisted') ? 'selected' : ''; ?>>Shortlisted</option>
                                                                                <option value="selected" <?php echo ($app['status'] == 'selected') ? 'selected' : ''; ?>>Selected</option>
                                                                                <option value="rejected" <?php echo ($app['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                                            </select>
                                                                        </div>
                                                                        <button type="submit" class="btn btn-primary">Update Status</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center">No applications found for this student.</p>
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
