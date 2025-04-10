<?php
/**
 * Helper functions for VIT Placement Portal
 */

/**
 * Format date in a readable format
 * 
 * @param string $date Date string
 * @param bool $include_time Whether to include time
 * @return string Formatted date
 */
function format_date($date, $include_time = false) {
    if (empty($date)) return 'N/A';
    
    if ($include_time) {
        return date("d M Y, h:i A", strtotime($date));
    } else {
        return date("d M Y", strtotime($date));
    }
}

/**
 * Get status badge HTML
 * 
 * @param string $status Status text
 * @return string HTML for status badge
 */
function get_status_badge($status) {
    switch ($status) {
        case 'applied':
            return '<span class="badge badge-applied">Applied</span>';
        case 'shortlisted':
            return '<span class="badge badge-shortlisted">Shortlisted</span>';
        case 'selected':
            return '<span class="badge badge-selected">Selected</span>';
        case 'rejected':
            return '<span class="badge badge-rejected">Rejected</span>';
        default:
            return '<span class="badge badge-secondary">Unknown</span>';
    }
}

/**
 * Get time remaining until deadline
 * 
 * @param string $deadline Deadline timestamp
 * @return string Time remaining text
 */
function get_time_remaining($deadline) {
    $deadline_time = strtotime($deadline);
    $current_time = time();
    
    if ($deadline_time < $current_time) {
        return 'Expired';
    }
    
    $remaining_seconds = $deadline_time - $current_time;
    
    // Convert to days, hours, minutes
    $days = floor($remaining_seconds / (60 * 60 * 24));
    $hours = floor(($remaining_seconds - ($days * 60 * 60 * 24)) / (60 * 60));
    $minutes = floor(($remaining_seconds - ($days * 60 * 60 * 24) - ($hours * 60 * 60)) / 60);
    
    if ($days > 0) {
        return $days . ' day' . ($days > 1 ? 's' : '') . ' remaining';
    } elseif ($hours > 0) {
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' remaining';
    } else {
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' remaining';
    }
}

/**
 * Check if student has already applied to a job
 * 
 * @param mysqli $conn Database connection
 * @param int $student_id Student ID
 * @param int $job_id Job ID
 * @return bool True if already applied, false otherwise
 */
function has_applied($conn, $student_id, $job_id) {
    $query = "SELECT * FROM applications WHERE student_id = ? AND job_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $student_id, $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

/**
 * Get job application statistics
 * 
 * @param mysqli $conn Database connection
 * @param int $job_id Job ID
 * @return array Statistics array with counts
 */
function get_job_statistics($conn, $job_id) {
    $stats = [
        'total' => 0,
        'applied' => 0,
        'shortlisted' => 0,
        'selected' => 0,
        'rejected' => 0
    ];
    
    // Get total applications
    $query = "SELECT COUNT(*) as count FROM applications WHERE job_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stats['total'] = $result['count'];
    
    // Get status counts
    $statuses = ['applied', 'shortlisted', 'selected', 'rejected'];
    foreach ($statuses as $status) {
        $query = "SELECT COUNT(*) as count FROM applications WHERE job_id = ? AND status = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $job_id, $status);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stats[$status] = $result['count'];
    }
    
    return $stats;
}

/**
 * Get all applications for a job
 * 
 * @param mysqli $conn Database connection
 * @param int $job_id Job ID
 * @return mysqli_result Result set with applications
 */
function get_job_applications($conn, $job_id) {
    $query = "SELECT a.*, s.name as student_name, s.registration_number, s.email as student_email, 
              s.cgpa, s.branch, s.semester
              FROM applications a 
              JOIN students s ON a.student_id = s.id
              WHERE a.job_id = ?
              ORDER BY a.applied_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Generate a secure random password
 * 
 * @param int $length Password length
 * @return string Random password
 */
function generate_random_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $password = '';
    $max = strlen($chars) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }
    
    return $password;
}
?>
