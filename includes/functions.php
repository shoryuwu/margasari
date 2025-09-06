<?php
// Helper functions untuk website kelurahan

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function upload_file($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'pdf']) {
    $target_dir = rtrim($target_dir, '/') . '/';
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $filename;
    
    // Check file type
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    // Check file size (max 5MB)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'File terlalu besar (maksimal 5MB)'];
    }
    
    // Create directory if not exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Gagal mengupload file'];
    }
}

function format_date($date, $format = 'd F Y') {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp)];
    $year = date('Y', $timestamp);
    
    return $day . ' ' . $month . ' ' . $year;
}

function get_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning">Menunggu</span>',
        'proses' => '<span class="badge bg-info">Proses</span>',
        'selesai' => '<span class="badge bg-success">Selesai</span>',
        'ditolak' => '<span class="badge bg-danger">Ditolak</span>',
        'draft' => '<span class="badge bg-secondary">Draft</span>',
        'published' => '<span class="badge bg-primary">Published</span>'
    ];
    
    return isset($badges[$status]) ? $badges[$status] : '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function set_flash_message($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function get_flash_messages() {
    $messages = isset($_SESSION['flash']) ? $_SESSION['flash'] : [];
    unset($_SESSION['flash']);
    return $messages;
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function get_admin_data() {
    return isset($_SESSION['admin_data']) ? $_SESSION['admin_data'] : null;
}

function paginate($query, $page = 1, $per_page = 10) {
    global $pdo;
    
    $offset = ($page - 1) * $per_page;
    
    // Get total count
    $count_query = preg_replace('/SELECT.*?FROM/i', 'SELECT COUNT(*) as total FROM', $query);
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute();
    $total_records = $count_stmt->fetch()['total'];
    
    // Get paginated data
    $query .= " LIMIT $per_page OFFSET $offset";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll();
    
    return [
        'data' => $data,
        'total_records' => $total_records,
        'current_page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total_records / $per_page)
    ];
}
?>
