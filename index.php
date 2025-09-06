<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Routing sederhana
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Cek halaman admin
if (strpos($page, 'admin') === 0) {
    // Cek login admin
    if (!isset($_SESSION['admin_logged_in']) && $page !== 'admin-login') {
        $page = 'admin-login';
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelurahan Margasari - Balikpapan Barat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php if (strpos($page, 'admin') !== 0): ?>
        <?php include 'includes/header.php'; ?>
        <?php include 'includes/navbar.php'; ?>
    <?php endif; ?>
    
    <main>
        <?php
        $file_path = "pages/" . str_replace('-', '_', $page) . ".php";
        if (file_exists($file_path)) {
            include $file_path;
        } else {
            include 'pages/404.php';
        }
        ?>
    </main>
    
    <?php if (strpos($page, 'admin') !== 0): ?>
        <?php include 'includes/footer.php'; ?>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
