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
        $pengaduan_id = (int)($_POST['pengaduan_id'] ?? 0);
        $action = $_POST['action'];
        
        try {
            if ($action === 'update_status') {
                $new_status = $_POST['status'] ?? '';
                $catatan = sanitize_input($_POST['catatan_admin'] ?? '');
                
                if (in_array($new_status, ['pending', 'proses', 'selesai', 'ditolak'])) {
                    $update_fields = ['status = ?', 'catatan_admin = ?', 'processed_by = ?'];
                    $update_values = [$new_status, $catatan, $admin['id']];
                    
                    if ($new_status === 'proses') {
                        $update_fields[] = 'tanggal_proses = NOW()';
                    } elseif ($new_status === 'selesai') {
                        $update_fields[] = 'tanggal_selesai = NOW()';
                    }
                    
                    $stmt = $pdo->prepare("
                        UPDATE pengaduan 
                        SET " . implode(', ', $update_fields) . "
                        WHERE id = ?
                    ");
                    
                    $update_values[] = $pengaduan_id;
                    $stmt->execute($update_values);
                    
                    $success = 'Status pengaduan berhasil diperbarui';
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
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($status_filter) && in_array($status_filter, ['pending', 'proses', 'selesai', 'ditolak'])) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(nama_pengadu LIKE ? OR judul LIKE ? OR jenis_pengaduan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

try {
    // Get total records
    $count_query = "SELECT COUNT(*) as total FROM pengaduan $where_clause";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    
    $total_pages = ceil($total_records / $per_page);
    $offset = ($page_num - 1) * $per_page;
    
    // Get pengaduan data
    $query = "
        SELECT p.*, au.name as processed_by_name
        FROM pengaduan p
        LEFT JOIN admin_users au ON p.processed_by = au.id
        $where_clause
        ORDER BY p.tanggal_pengaduan DESC
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $pengaduan_list = $stmt->fetchAll();
    
    // Get statistics
    $stats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
            SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
            SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
        FROM pengaduan
    ")->fetch();
    
} catch (Exception $e) {
    $pengaduan_list = [];
    $total_records = 0;
    $total_pages = 0;
    $stats = ['total' => 0, 'pending' => 0, 'proses' => 0, 'selesai' => 0, 'ditolak' => 0];
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
    <title>Kelola Pengaduan - Admin Kelurahan Margasari</title>
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
                        <a class="nav-link active" href="?page=admin-pengaduan">
                            <i class="fas fa-comment-dots"></i> Pengaduan
                            <?php if ($stats['pending'] > 0): ?>
                                <span class="badge bg-warning"><?php echo $stats['pending']; ?></span>
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
                            <i class="fas fa-comment-dots text-primary"></i> 
                            Kelola Pengaduan Masyarakat
                        </h2>
                        <div class="text-muted">
                            Total: <?php echo $total_records; ?> pengaduan
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h3 class="text-warning"><?php echo $stats['pending']; ?></h3>
                                    <p class="mb-0">
                                        <i class="fas fa-clock text-warning"></i> Pending
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h3 class="text-info"><?php echo $stats['proses']; ?></h3>
                                    <p class="mb-0">
                                        <i class="fas fa-cogs text-info"></i> Proses
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h3 class="text-success"><?php echo $stats['selesai']; ?></h3>
                                    <p class="mb-0">
                                        <i class="fas fa-check-circle text-success"></i> Selesai
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <h3 class="text-danger"><?php echo $stats['ditolak']; ?></h3>
                                    <p class="mb-0">
                                        <i class="fas fa-times-circle text-danger"></i> Ditolak
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter and Search -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <input type="hidden" name="page" value="admin-pengaduan">
                                
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>
                                            Pending
                                        </option>
                                        <option value="proses" <?php echo $status_filter == 'proses' ? 'selected' : ''; ?>>
                                            Proses
                                        </option>
                                        <option value="selesai" <?php echo $status_filter == 'selesai' ? 'selected' : ''; ?>>
                                            Selesai
                                        </option>
                                        <option value="ditolak" <?php echo $status_filter == 'ditolak' ? 'selected' : ''; ?>>
                                            Ditolak
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Cari</label>
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>"
                                           placeholder="Cari nama, judul, atau jenis pengaduan...">
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

                    <!-- Pengaduan List -->
                    <?php if (count($pengaduan_list) > 0): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Daftar Pengaduan</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>ID</th>
                                                <th>Tanggal</th>
                                                <th>Nama Pengadu</th>
                                                <th>Jenis</th>
                                                <th>Judul</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pengaduan_list as $pengaduan): ?>
                                                <tr>
                                                    <td><strong>#<?php echo $pengaduan['id']; ?></strong></td>
                                                    <td>
                                                        <small><?php echo format_date($pengaduan['tanggal_pengaduan']); ?></small>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($pengaduan['nama_pengadu']); ?></strong><br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($pengaduan['phone']); ?>
                                                            <?php if ($pengaduan['rt'] && $pengaduan['rw']): ?>
                                                                <br>RT <?php echo $pengaduan['rt']; ?>/RW <?php echo $pengaduan['rw']; ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?php echo ucfirst($pengaduan['jenis_pengaduan']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($pengaduan['judul']); ?></strong>
                                                        <?php if ($pengaduan['foto']): ?>
                                                            <br><small class="text-info">
                                                                <i class="fas fa-camera"></i> Ada foto
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo get_status_badge($pengaduan['status']); ?>
                                                        <?php if ($pengaduan['processed_by_name']): ?>
                                                            <br><small class="text-muted">
                                                                oleh <?php echo htmlspecialchars($pengaduan['processed_by_name']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#detailModal<?php echo $pengaduan['id']; ?>">
                                                            <i class="fas fa-eye"></i> Detail
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
                            <nav class="mt-4" aria-label="Halaman pengaduan">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page_num > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" 
                                               href="?page=admin-pengaduan&p=<?php echo ($page_num - 1); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">
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
                                               href="?page=admin-pengaduan&p=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page_num < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" 
                                               href="?page=admin-pengaduan&p=<?php echo ($page_num + 1); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">
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
                                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">Tidak Ada Pengaduan</h4>
                                <p class="text-muted">
                                    <?php if (!empty($search) || !empty($status_filter)): ?>
                                        Tidak ditemukan pengaduan yang sesuai dengan filter.
                                    <?php else: ?>
                                        Belum ada pengaduan yang masuk.
                                    <?php endif; ?>
                                </p>
                                
                                <?php if (!empty($search) || !empty($status_filter)): ?>
                                    <a href="?page=admin-pengaduan" class="btn btn-primary">
                                        <i class="fas fa-refresh"></i> Lihat Semua
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modals -->
    <?php foreach ($pengaduan_list as $pengaduan): ?>
        <div class="modal fade" id="detailModal<?php echo $pengaduan['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-comment-dots"></i> 
                            Detail Pengaduan #<?php echo $pengaduan['id']; ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informasi Pengadu</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Nama:</strong></td>
                                        <td><?php echo htmlspecialchars($pengaduan['nama_pengadu']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>HP:</strong></td>
                                        <td><?php echo htmlspecialchars($pengaduan['phone']); ?></td>
                                    </tr>
                                    <?php if ($pengaduan['email']): ?>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td><?php echo htmlspecialchars($pengaduan['email']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>RT/RW:</strong></td>
                                        <td>RT <?php echo $pengaduan['rt']; ?>/RW <?php echo $pengaduan['rw']; ?></td>
                                    </tr>
                                    <?php if ($pengaduan['alamat']): ?>
                                        <tr>
                                            <td><strong>Alamat:</strong></td>
                                            <td><?php echo htmlspecialchars($pengaduan['alamat']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Informasi Pengaduan</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Tanggal:</strong></td>
                                        <td><?php echo format_date($pengaduan['tanggal_pengaduan']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jenis:</strong></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo ucfirst($pengaduan['jenis_pengaduan']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td><?php echo get_status_badge($pengaduan['status']); ?></td>
                                    </tr>
                                    <?php if ($pengaduan['processed_by_name']): ?>
                                        <tr>
                                            <td><strong>Diproses:</strong></td>
                                            <td><?php echo htmlspecialchars($pengaduan['processed_by_name']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Judul Pengaduan</h6>
                            <p class="fw-bold"><?php echo htmlspecialchars($pengaduan['judul']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Isi Pengaduan</h6>
                            <div class="bg-light p-3 rounded">
                                <?php echo nl2br(htmlspecialchars($pengaduan['isi_pengaduan'])); ?>
                            </div>
                        </div>
                        
                        <?php if ($pengaduan['foto']): ?>
                            <div class="mb-3">
                                <h6>Foto Dokumentasi</h6>
                                <?php 
                                $foto_path = "uploads/pengaduan/" . htmlspecialchars($pengaduan['foto']);
                                $full_foto_path = __DIR__ . "/../" . $foto_path;
                                ?>
                                <?php if (file_exists($full_foto_path)): ?>
                                    <div class="text-center">
                                        <img src="<?php echo $foto_path; ?>" 
                                             class="img-fluid rounded shadow" 
                                             alt="Dokumentasi Pengaduan" 
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
                                        File gambar tidak ditemukan: <?php echo htmlspecialchars($pengaduan['foto']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($pengaduan['catatan_admin']): ?>
                            <div class="mb-3">
                                <h6>Catatan Admin</h6>
                                <div class="alert alert-info">
                                    <?php echo nl2br(htmlspecialchars($pengaduan['catatan_admin'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Update Status Form -->
                        <div class="border-top pt-3">
                            <h6>Update Status</h6>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="pengaduan_id" value="<?php echo $pengaduan['id']; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status Baru</label>
                                        <select class="form-select" name="status" required>
                                            <option value="pending" <?php echo $pengaduan['status'] == 'pending' ? 'selected' : ''; ?>>
                                                Pending
                                            </option>
                                            <option value="proses" <?php echo $pengaduan['status'] == 'proses' ? 'selected' : ''; ?>>
                                                Proses
                                            </option>
                                            <option value="selesai" <?php echo $pengaduan['status'] == 'selesai' ? 'selected' : ''; ?>>
                                                Selesai
                                            </option>
                                            <option value="ditolak" <?php echo $pengaduan['status'] == 'ditolak' ? 'selected' : ''; ?>>
                                                Ditolak
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Catatan Admin</label>
                                        <textarea class="form-control" name="catatan_admin" rows="3" 
                                                  placeholder="Tambahkan catatan atau keterangan..."><?php echo htmlspecialchars($pengaduan['catatan_admin']); ?></textarea>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

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
                    <img id="modalImage" src="" class="img-fluid rounded" alt="Dokumentasi Pengaduan">
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
        downloadLink.download = imageSrc.split('/').pop(); // Get filename from path
        
        modal.show();
    }
    
    // Auto-refresh notifications for new pengaduan
    setInterval(function() {
        // You can implement WebSocket or AJAX polling here
        // For now, just refresh the pending count badge
    }, 60000); // Refresh every minute
    
    // Confirmation for status changes
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (this.querySelector('select[name="status"]')) {
                const status = this.querySelector('select[name="status"]').value;
                const pengaduanId = this.querySelector('input[name="pengaduan_id"]').value;
                
                if (!confirm(`Apakah Anda yakin ingin mengubah status pengaduan #${pengaduanId} menjadi "${status}"?`)) {
                    e.preventDefault();
                }
            }
        });
    });
    
    // Image loading error handling
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('img[src*="uploads/pengaduan"]');
        images.forEach(img => {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                const container = this.closest('.text-center') || this.parentElement;
                if (container) {
                    container.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Gambar tidak dapat dimuat. File mungkin tidak ada atau rusak.
                        </div>
                    `;
                }
            });
            
            img.addEventListener('load', function() {
                // Image loaded successfully
                console.log('Image loaded: ' + this.src);
            });
        });
    });
    </script>
</body>
</html>
