<?php
// Cek login admin
if (!is_admin_logged_in()) {
    redirect('?page=admin-login');
}

$admin = get_admin_data();
$success = '';
$error = '';

// Handle actions
if ($_POST && isset($_POST['action'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Token keamanan tidak valid';
    } else {
        $action = $_POST['action'];
        
        try {
            if ($action === 'add_kegiatan') {
                $rt_number = sanitize_input($_POST['rt_number'] ?? '');
                $rw_number = sanitize_input($_POST['rw_number'] ?? '');
                $judul_kegiatan = sanitize_input($_POST['judul_kegiatan'] ?? '');
                $deskripsi_kegiatan = sanitize_input($_POST['deskripsi_kegiatan'] ?? '');
                $tanggal_kegiatan = sanitize_input($_POST['tanggal_kegiatan'] ?? '');
                $waktu_kegiatan = sanitize_input($_POST['waktu_kegiatan'] ?? '');
                $tempat_kegiatan = sanitize_input($_POST['tempat_kegiatan'] ?? '');
                $jumlah_peserta = (int)($_POST['jumlah_peserta'] ?? 0);
                $penanggung_jawab = sanitize_input($_POST['penanggung_jawab'] ?? '');
                $status = sanitize_input($_POST['status'] ?? 'draft');
                
                $foto_dokumentasi = null;
                
                // Handle file upload
                if (isset($_FILES['foto_dokumentasi']) && $_FILES['foto_dokumentasi']['error'] == 0) {
                    $upload_result = upload_file($_FILES['foto_dokumentasi'], 'uploads/activities', ['jpg', 'jpeg', 'png']);
                    if ($upload_result['success']) {
                        $foto_dokumentasi = $upload_result['filename'];
                    } else {
                        $error = $upload_result['message'];
                    }
                }
                
                if (empty($error)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO laporan_kegiatan (rt_number, rw_number, judul_kegiatan, deskripsi_kegiatan, tanggal_kegiatan, waktu_kegiatan, tempat_kegiatan, jumlah_peserta, penanggung_jawab, foto_dokumentasi, status, created_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $rt_number, $rw_number, $judul_kegiatan, $deskripsi_kegiatan,
                        $tanggal_kegiatan, $waktu_kegiatan ?: null, $tempat_kegiatan,
                        $jumlah_peserta ?: null, $penanggung_jawab, $foto_dokumentasi,
                        $status, $admin['id']
                    ]);
                    
                    $success = 'Kegiatan berhasil ditambahkan';
                }
                
            } elseif ($action === 'update_status') {
                $kegiatan_id = (int)($_POST['kegiatan_id'] ?? 0);
                $new_status = $_POST['status'] ?? '';
                
                if (in_array($new_status, ['draft', 'published']) && $kegiatan_id > 0) {
                    $stmt = $pdo->prepare("UPDATE laporan_kegiatan SET status = ?, created_by = ? WHERE id = ?");
                    $stmt->execute([$new_status, $admin['id'], $kegiatan_id]);
                    
                    $success = 'Status kegiatan berhasil diperbarui';
                }
                
            } elseif ($action === 'delete_kegiatan') {
                $kegiatan_id = (int)($_POST['kegiatan_id'] ?? 0);
                
                if ($kegiatan_id > 0) {
                    // Get file info before delete
                    $stmt = $pdo->prepare("SELECT foto_dokumentasi FROM laporan_kegiatan WHERE id = ?");
                    $stmt->execute([$kegiatan_id]);
                    $kegiatan = $stmt->fetch();
                    
                    // Delete from database
                    $stmt = $pdo->prepare("DELETE FROM laporan_kegiatan WHERE id = ?");
                    $stmt->execute([$kegiatan_id]);
                    
                    // Delete file if exists
                    if ($kegiatan && $kegiatan['foto_dokumentasi']) {
                        $file_path = 'uploads/activities/' . $kegiatan['foto_dokumentasi'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                    
                    $success = 'Kegiatan berhasil dihapus';
                }
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Pagination and filtering
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$rt_filter = isset($_GET['rt']) ? sanitize_input($_GET['rt']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$month_filter = isset($_GET['month']) ? sanitize_input($_GET['month']) : '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($status_filter) && in_array($status_filter, ['draft', 'published'])) {
    $where_conditions[] = "lk.status = ?";
    $params[] = $status_filter;
}

if (!empty($rt_filter)) {
    $where_conditions[] = "lk.rt_number = ?";
    $params[] = $rt_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(lk.judul_kegiatan LIKE ? OR lk.deskripsi_kegiatan LIKE ? OR lk.tempat_kegiatan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($month_filter)) {
    $where_conditions[] = "DATE_FORMAT(lk.tanggal_kegiatan, '%Y-%m') = ?";
    $params[] = $month_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

try {
    // Get total records
    $count_query = "SELECT COUNT(*) as total FROM laporan_kegiatan lk $where_clause";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    
    $total_pages = ceil($total_records / $per_page);
    $offset = ($page_num - 1) * $per_page;
    
    // Get kegiatan data
    $query = "
        SELECT lk.*, rd.ketua_rt, au.name as created_by_name
        FROM laporan_kegiatan lk
        LEFT JOIN rt_data rd ON lk.rt_number = rd.rt_number AND lk.rw_number = rd.rw_number
        LEFT JOIN admin_users au ON lk.created_by = au.id
        $where_clause
        ORDER BY lk.tanggal_kegiatan DESC, lk.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $kegiatan_list = $stmt->fetchAll();
    
    // Get statistics
    $stats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN MONTH(tanggal_kegiatan) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) as bulan_ini,
            SUM(CASE WHEN DATE(tanggal_kegiatan) = CURRENT_DATE() THEN 1 ELSE 0 END) as hari_ini
        FROM laporan_kegiatan
    ")->fetch();
    
    // Get RT options
    $rt_stmt = $pdo->query("SELECT DISTINCT rt_number FROM rt_data ORDER BY rt_number");
    $rt_options = $rt_stmt->fetchAll();
    
} catch (Exception $e) {
    $kegiatan_list = [];
    $total_records = 0;
    $total_pages = 0;
    $stats = ['total' => 0, 'draft' => 0, 'published' => 0, 'bulan_ini' => 0, 'hari_ini' => 0];
    $rt_options = [];
    $error = 'Terjadi kesalahan database: ' . $e->getMessage();
}

// Flash messages
$flash_messages = get_flash_messages();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kegiatan RT - Admin Kelurahan Margasari</title>
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
                        <a class="nav-link" href="?page=admin-dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="?page=admin-pengaduan">
                            <i class="fas fa-comment-dots"></i> Pengaduan
                        </a>
                        <a class="nav-link" href="?page=admin-surat">
                            <i class="fas fa-file-alt"></i> Permintaan Surat
                        </a>
                        <a class="nav-link active" href="?page=admin-kegiatan">
                            <i class="fas fa-calendar-alt"></i> Kegiatan RT
                            <?php if ($stats['draft'] > 0): ?>
                                <span class="badge bg-warning"><?php echo $stats['draft']; ?></span>
                            <?php endif; ?>
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

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>
                            <i class="fas fa-calendar-alt text-success"></i> 
                            Kelola Kegiatan RT
                        </h2>
                        <div>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addKegiatanModal">
                                <i class="fas fa-plus"></i> Tambah Kegiatan
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h3 class="text-primary"><?php echo $stats['total']; ?></h3>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar-check text-primary"></i> Total Kegiatan
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h3 class="text-warning"><?php echo $stats['draft']; ?></h3>
                                    <p class="mb-0">
                                        <i class="fas fa-edit text-warning"></i> Draft
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h3 class="text-success"><?php echo $stats['published']; ?></h3>
                                    <p class="mb-0">
                                        <i class="fas fa-globe text-success"></i> Published
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h3 class="text-info"><?php echo $stats['bulan_ini']; ?></h3>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar text-info"></i> Bulan Ini
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter and Search -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <input type="hidden" name="page" value="admin-kegiatan">
                                
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>
                                            Draft
                                        </option>
                                        <option value="published" <?php echo $status_filter == 'published' ? 'selected' : ''; ?>>
                                            Published
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">RT</label>
                                    <select class="form-select" name="rt">
                                        <option value="">Semua RT</option>
                                        <?php foreach ($rt_options as $rt): ?>
                                            <option value="<?php echo htmlspecialchars($rt['rt_number']); ?>"
                                                    <?php echo $rt_filter == $rt['rt_number'] ? 'selected' : ''; ?>>
                                                RT <?php echo htmlspecialchars($rt['rt_number']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Bulan</label>
                                    <input type="month" class="form-control" name="month" 
                                           value="<?php echo htmlspecialchars($month_filter); ?>">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Cari</label>
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>"
                                           placeholder="Cari judul, deskripsi, atau tempat...">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary d-block">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Kegiatan List -->
                    <?php if (count($kegiatan_list) > 0): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Daftar Kegiatan RT</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-success">
                                            <tr>
                                                <th>ID</th>
                                                <th>Tanggal</th>
                                                <th>RT/RW</th>
                                                <th>Judul Kegiatan</th>
                                                <th>Tempat</th>
                                                <th>Peserta</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($kegiatan_list as $kegiatan): ?>
                                                <tr>
                                                    <td><strong>#<?php echo $kegiatan['id']; ?></strong></td>
                                                    <td>
                                                        <strong><?php echo format_date($kegiatan['tanggal_kegiatan']); ?></strong><br>
                                                        <?php if ($kegiatan['waktu_kegiatan']): ?>
                                                            <small class="text-muted">
                                                                <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($kegiatan['waktu_kegiatan'])); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            RT <?php echo $kegiatan['rt_number']; ?>/RW <?php echo $kegiatan['rw_number']; ?>
                                                        </span>
                                                        <?php if ($kegiatan['ketua_rt']): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($kegiatan['ketua_rt']); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?></strong>
                                                        <?php if ($kegiatan['foto_dokumentasi']): ?>
                                                            <br><small class="text-success">
                                                                <i class="fas fa-camera"></i> Ada foto
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($kegiatan['tempat_kegiatan']): ?>
                                                            <i class="fas fa-map-marker-alt text-danger"></i>
                                                            <?php echo htmlspecialchars($kegiatan['tempat_kegiatan']); ?>
                                                        <?php else: ?>
                                                            <small class="text-muted">-</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($kegiatan['jumlah_peserta']): ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-users"></i> <?php echo $kegiatan['jumlah_peserta']; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <small class="text-muted">-</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo get_status_badge($kegiatan['status']); ?>
                                                        <?php if ($kegiatan['created_by_name']): ?>
                                                            <br><small class="text-muted">
                                                                oleh <?php echo htmlspecialchars($kegiatan['created_by_name']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary mb-1" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#detailModal<?php echo $kegiatan['id']; ?>">
                                                            <i class="fas fa-eye"></i> Detail
                                                        </button>
                                                        <br>
                                                        <button class="btn btn-sm btn-danger" 
                                                                onclick="confirmDelete('kegiatan RT', '<?php echo $kegiatan['judul_kegiatan']; ?>', <?php echo $kegiatan['id']; ?>)">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav class="mt-4" aria-label="Halaman kegiatan">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page_num > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" 
                                               href="?page=admin-kegiatan&p=<?php echo ($page_num - 1); ?>&status=<?php echo urlencode($status_filter); ?>&rt=<?php echo urlencode($rt_filter); ?>&month=<?php echo urlencode($month_filter); ?>&search=<?php echo urlencode($search); ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $start_page = max(1, $page_num - 2);
                                    $end_page = min($total_pages, $page_num + 2);
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <li class="page-item <?php echo ($i == $page_num) ? 'active' : ''; ?>">
                                            <a class="page-link" 
                                               href="?page=admin-kegiatan&p=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&rt=<?php echo urlencode($rt_filter); ?>&month=<?php echo urlencode($month_filter); ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page_num < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" 
                                               href="?page=admin-kegiatan&p=<?php echo ($page_num + 1); ?>&status=<?php echo urlencode($status_filter); ?>&rt=<?php echo urlencode($rt_filter); ?>&month=<?php echo urlencode($month_filter); ?>&search=<?php echo urlencode($search); ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- No Data -->
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">Tidak Ada Kegiatan</h4>
                                <p class="text-muted">
                                    <?php if (!empty($search) || !empty($status_filter) || !empty($rt_filter) || !empty($month_filter)): ?>
                                        Tidak ditemukan kegiatan yang sesuai dengan filter.
                                    <?php else: ?>
                                        Belum ada kegiatan RT yang terdaftar.
                                    <?php endif; ?>
                                </p>
                                
                                <?php if (!empty($search) || !empty($status_filter) || !empty($rt_filter) || !empty($month_filter)): ?>
                                    <a href="?page=admin-kegiatan" class="btn btn-primary">
                                        <i class="fas fa-refresh"></i> Lihat Semua
                                    </a>
                                <?php endif; ?>
                                
                                <button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#addKegiatanModal">
                                    <i class="fas fa-plus"></i> Tambah Kegiatan Pertama
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Add Kegiatan Modal -->
    <div class="modal fade" id="addKegiatanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> Tambah Kegiatan RT
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="add_kegiatan">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RT <span class="text-danger">*</span></label>
                                <select class="form-select" name="rt_number" required>
                                    <option value="">Pilih RT</option>
                                    <?php foreach ($rt_options as $rt): ?>
                                        <option value="<?php echo htmlspecialchars($rt['rt_number']); ?>">
                                            RT <?php echo htmlspecialchars($rt['rt_number']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RW <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="rw_number" 
                                       placeholder="001" maxlength="3" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Judul Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="judul_kegiatan" 
                                   placeholder="Contoh: Kerja Bakti Lingkungan RT 001" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                            <textarea class="form-control auto-resize" name="deskripsi_kegiatan" rows="3" 
                                      placeholder="Jelaskan detail kegiatan yang dilaksanakan..." required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Kegiatan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_kegiatan" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Kegiatan</label>
                                <input type="time" class="form-control" name="waktu_kegiatan">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tempat Kegiatan</label>
                            <input type="text" class="form-control" name="tempat_kegiatan" 
                                   placeholder="Contoh: Balai RT 001 atau Jl. Margasari">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah Peserta</label>
                                <input type="number" class="form-control" name="jumlah_peserta" 
                                       min="1" placeholder="Contoh: 25">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Penanggung Jawab</label>
                                <input type="text" class="form-control" name="penanggung_jawab" 
                                       placeholder="Nama penanggung jawab kegiatan">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Foto Dokumentasi</label>
                            <input type="file" class="form-control" name="foto_dokumentasi" accept=".jpg,.jpeg,.png">
                            <div class="form-text">Format: JPG, JPEG, PNG. Maksimal 5MB.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="draft">Draft (belum dipublikasi)</option>
                                <option value="published">Published (tampil di website)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan Kegiatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail Modals -->
    <?php foreach ($kegiatan_list as $kegiatan): ?>
        <div class="modal fade" id="detailModal<?php echo $kegiatan['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-calendar-alt"></i> 
                            Detail Kegiatan #<?php echo $kegiatan['id']; ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informasi Kegiatan</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>RT/RW:</strong></td>
                                        <td>RT <?php echo $kegiatan['rt_number']; ?>/RW <?php echo $kegiatan['rw_number']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal:</strong></td>
                                        <td><?php echo format_date($kegiatan['tanggal_kegiatan']); ?></td>
                                    </tr>
                                    <?php if ($kegiatan['waktu_kegiatan']): ?>
                                        <tr>
                                            <td><strong>Waktu:</strong></td>
                                            <td><?php echo date('H:i', strtotime($kegiatan['waktu_kegiatan'])); ?> WIB</td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if ($kegiatan['tempat_kegiatan']): ?>
                                        <tr>
                                            <td><strong>Tempat:</strong></td>
                                            <td><?php echo htmlspecialchars($kegiatan['tempat_kegiatan']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if ($kegiatan['jumlah_peserta']): ?>
                                        <tr>
                                            <td><strong>Peserta:</strong></td>
                                            <td><?php echo $kegiatan['jumlah_peserta']; ?> orang</td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Informasi Lainnya</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td><?php echo get_status_badge($kegiatan['status']); ?></td>
                                    </tr>
                                    <?php if ($kegiatan['penanggung_jawab']): ?>
                                        <tr>
                                            <td><strong>PJ:</strong></td>
                                            <td><?php echo htmlspecialchars($kegiatan['penanggung_jawab']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if ($kegiatan['ketua_rt']): ?>
                                        <tr>
                                            <td><strong>Ketua RT:</strong></td>
                                            <td><?php echo htmlspecialchars($kegiatan['ketua_rt']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if ($kegiatan['created_by_name']): ?>
                                        <tr>
                                            <td><strong>Input by:</strong></td>
                                            <td><?php echo htmlspecialchars($kegiatan['created_by_name']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Judul Kegiatan</h6>
                            <p class="fw-bold"><?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Deskripsi Kegiatan</h6>
                            <div class="bg-light p-3 rounded">
                                <?php echo nl2br(htmlspecialchars($kegiatan['deskripsi_kegiatan'])); ?>
                            </div>
                        </div>
                        
                        <?php if ($kegiatan['foto_dokumentasi']): ?>
                            <div class="mb-3">
                                <h6>Foto Dokumentasi</h6>
                                <?php 
                                $foto_path = "uploads/activities/" . htmlspecialchars($kegiatan['foto_dokumentasi']);
                                $full_foto_path = __DIR__ . "/../" . $foto_path;
                                ?>
                                <?php if (file_exists($full_foto_path)): ?>
                                    <div class="text-center">
                                        <img src="<?php echo $foto_path; ?>" 
                                             class="img-fluid rounded shadow" 
                                             alt="Dokumentasi Kegiatan" 
                                             style="max-height: 400px; cursor: pointer;"
                                             onclick="showImageModal('<?php echo $foto_path; ?>')">
                                        <br>
                                        <small class="text-muted mt-2 d-block">
                                            <i class="fas fa-search-plus"></i> Klik gambar untuk memperbesar
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        File gambar tidak ditemukan: <?php echo htmlspecialchars($kegiatan['foto_dokumentasi']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Update Status Form -->
                        <div class="border-top pt-3">
                            <h6>Update Status</h6>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="kegiatan_id" value="<?php echo $kegiatan['id']; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-select" name="status" required>
                                            <option value="draft" <?php echo $kegiatan['status'] == 'draft' ? 'selected' : ''; ?>>
                                                Draft
                                            </option>
                                            <option value="published" <?php echo $kegiatan['status'] == 'published' ? 'selected' : ''; ?>>
                                                Published
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Status
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Delete Form (Hidden) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="hidden" name="action" value="delete_kegiatan">
        <input type="hidden" name="kegiatan_id" id="deleteKegiatanId">
    </form>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-image"></i> Foto Dokumentasi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid rounded" alt="Dokumentasi Kegiatan">
                </div>
                <div class="modal-footer">
                    <a id="downloadImage" href="" download class="btn btn-success">
                        <i class="fas fa-download"></i> Download Gambar
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
    // Show image in modal
    function showImageModal(imageSrc) {
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        const modalImage = document.getElementById('modalImage');
        const downloadLink = document.getElementById('downloadImage');
        
        modalImage.src = imageSrc;
        downloadLink.href = imageSrc;
        downloadLink.download = imageSrc.split('/').pop();
        
        modal.show();
    }
    
    // Delete confirmation
    function confirmDelete(itemType, itemName, kegiatanId) {
        if (confirm(`Apakah Anda yakin ingin menghapus ${itemType} "${itemName}"?\n\nTindakan ini tidak dapat dibatalkan dan akan menghapus foto dokumentasi juga.`)) {
            document.getElementById('deleteKegiatanId').value = kegiatanId;
            document.getElementById('deleteForm').submit();
        }
    }
    
    // Auto-set today's date for new kegiatan
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.querySelector('input[name="tanggal_kegiatan"]');
        if (dateInput && !dateInput.value) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
    });
    </script>
</body>
</html>
