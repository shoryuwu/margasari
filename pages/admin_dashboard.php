<?php
// Cek login admin
if (!is_admin_logged_in()) {
    redirect('?page=admin-login');
}

$admin = get_admin_data();

// Ambil statistik dashboard
try {
    // Total pengaduan
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengaduan");
    $total_pengaduan = $stmt->fetch()['total'];
    
    // Pengaduan pending
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengaduan WHERE status = 'pending'");
    $pengaduan_pending = $stmt->fetch()['total'];
    
    // Total kegiatan RT bulan ini
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM laporan_kegiatan WHERE MONTH(tanggal_kegiatan) = MONTH(CURRENT_DATE())");
    $kegiatan_bulan_ini = $stmt->fetch()['total'];
    
    // Total permintaan surat
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM permintaan_surat");
    $total_permintaan_surat = $stmt->fetch()['total'];
    
    // Pengaduan terbaru
    $stmt = $pdo->query("SELECT * FROM pengaduan ORDER BY tanggal_pengaduan DESC LIMIT 5");
    $pengaduan_terbaru = $stmt->fetchAll();
    
    // Kegiatan RT terbaru
    $stmt = $pdo->query("
        SELECT lk.*, rd.ketua_rt 
        FROM laporan_kegiatan lk 
        LEFT JOIN rt_data rd ON lk.rt_number = rd.rt_number AND lk.rw_number = rd.rw_number
        ORDER BY lk.created_at DESC 
        LIMIT 5
    ");
    $kegiatan_terbaru = $stmt->fetchAll();
    
} catch (Exception $e) {
    $total_pengaduan = 0;
    $pengaduan_pending = 0;
    $kegiatan_bulan_ini = 0;
    $total_permintaan_surat = 0;
    $pengaduan_terbaru = [];
    $kegiatan_terbaru = [];
}

// Flash messages
$flash_messages = get_flash_messages();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kelurahan Margasari</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="?page=admin-dashboard">
                <i class="fas fa-tachometer-alt"></i> Admin Panel
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($admin['name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="index.php"><i class="fas fa-home"></i> Lihat Website</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?page=admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="admin-sidebar">
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="?page=admin-dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="?page=admin-pengaduan">
                            <i class="fas fa-comment-dots"></i> Pengaduan
                            <?php if ($pengaduan_pending > 0): ?>
                                <span class="badge bg-warning"><?php echo $pengaduan_pending; ?></span>
                            <?php endif; ?>
                        </a>
                        <a class="nav-link" href="?page=admin-surat">
                            <i class="fas fa-file-alt"></i> Permintaan Surat
                        </a>
                        <a class="nav-link" href="?page=admin-kegiatan">
                            <i class="fas fa-calendar-alt"></i> Kegiatan RT
                        </a>
                        <a class="nav-link" href="?page=admin-template">
                            <i class="fas fa-file-contract"></i> Template Surat
                        </a>
                        <a class="nav-link" href="?page=admin-rt">
                            <i class="fas fa-map-marker-alt"></i> Data RT/RW
                        </a>
                        <a class="nav-link" href="?page=admin-fasilitas">
                            <i class="fas fa-building"></i> Fasilitas Umum
                        </a>
                        <?php if ($admin['role'] == 'super_admin'): ?>
                            <a class="nav-link" href="?page=admin-users">
                                <i class="fas fa-users-cog"></i> Manajemen User
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="admin-content">
                    
                    <!-- Flash Messages -->
                    <?php foreach ($flash_messages as $type => $message): ?>
                        <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endforeach; ?>

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Dashboard Admin</h2>
                        <div class="text-muted">
                            <i class="fas fa-calendar"></i> <?php echo format_date(date('Y-m-d')); ?>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo $total_pengaduan; ?></h4>
                                            <p class="mb-0">Total Pengaduan</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-comment-dots fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex">
                                    <span class="small">
                                        <i class="fas fa-clock"></i> Pending: <?php echo $pengaduan_pending; ?>
                                    </span>
                                    <a href="?page=admin-pengaduan" class="ms-auto text-white">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo $kegiatan_bulan_ini; ?></h4>
                                            <p class="mb-0">Kegiatan Bulan Ini</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex">
                                    <span class="small">
                                        <i class="fas fa-calendar"></i> <?php echo date('F Y'); ?>
                                    </span>
                                    <a href="?page=admin-kegiatan" class="ms-auto text-white">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo $total_permintaan_surat; ?></h4>
                                            <p class="mb-0">Permintaan Surat</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-file-alt fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex">
                                    <span class="small">
                                        <i class="fas fa-file"></i> Semua status
                                    </span>
                                    <a href="?page=admin-surat" class="ms-auto text-white">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo count($pengaduan_terbaru); ?></h4>
                                            <p class="mb-0">Aktivitas Hari Ini</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex">
                                    <span class="small">
                                        <i class="fas fa-sync"></i> Real-time
                                    </span>
                                    <a href="#" class="ms-auto text-white">
                                        <i class="fas fa-refresh"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="row">
                        <!-- Recent Complaints -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-comment-dots text-primary"></i> 
                                        Pengaduan Terbaru
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($pengaduan_terbaru) > 0): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($pengaduan_terbaru as $pengaduan): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($pengaduan['judul']); ?></h6>
                                                        <?php echo get_status_badge($pengaduan['status']); ?>
                                                    </div>
                                                    <p class="mb-1 small text-muted">
                                                        <?php echo htmlspecialchars($pengaduan['nama_pengadu']); ?> • 
                                                        <?php echo ucfirst($pengaduan['jenis_pengaduan']); ?>
                                                    </p>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock"></i> 
                                                        <?php echo format_date($pengaduan['tanggal_pengaduan']); ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="card-footer text-center">
                                            <a href="?page=admin-pengaduan" class="btn btn-outline-primary btn-sm">
                                                Lihat Semua Pengaduan
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center p-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3"></i>
                                            <p>Belum ada pengaduan</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activities -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-alt text-success"></i> 
                                        Kegiatan RT Terbaru
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (count($kegiatan_terbaru) > 0): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($kegiatan_terbaru as $kegiatan): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?></h6>
                                                        <?php echo get_status_badge($kegiatan['status']); ?>
                                                    </div>
                                                    <p class="mb-1 small text-muted">
                                                        RT <?php echo $kegiatan['rt_number']; ?>/RW <?php echo $kegiatan['rw_number']; ?>
                                                        <?php if ($kegiatan['ketua_rt']): ?>
                                                            • <?php echo htmlspecialchars($kegiatan['ketua_rt']); ?>
                                                        <?php endif; ?>
                                                    </p>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar"></i> 
                                                        <?php echo format_date($kegiatan['tanggal_kegiatan']); ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="card-footer text-center">
                                            <a href="?page=admin-kegiatan" class="btn btn-outline-success btn-sm">
                                                Lihat Semua Kegiatan
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center p-4 text-muted">
                                            <i class="fas fa-calendar fa-2x mb-3"></i>
                                            <p>Belum ada kegiatan</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
