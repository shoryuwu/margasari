<?php
// Pagination
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 6;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$rt_filter = isset($_GET['rt']) ? sanitize_input($_GET['rt']) : '';

// Build query
$where_conditions = ["status = 'published'"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(judul_kegiatan LIKE ? OR deskripsi_kegiatan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($rt_filter)) {
    $where_conditions[] = "rt_number = ?";
    $params[] = $rt_filter;
}

$where_clause = implode(' AND ', $where_conditions);

try {
    // Get total records
    $count_query = "
        SELECT COUNT(*) as total 
        FROM laporan_kegiatan lk 
        LEFT JOIN rt_data rd ON lk.rt_number = rd.rt_number AND lk.rw_number = rd.rw_number
        WHERE $where_clause
    ";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    
    $total_pages = ceil($total_records / $per_page);
    $offset = ($page_num - 1) * $per_page;
    
    // Get kegiatan data
    $query = "
        SELECT lk.*, rd.ketua_rt, rd.ketua_rt_phone
        FROM laporan_kegiatan lk 
        LEFT JOIN rt_data rd ON lk.rt_number = rd.rt_number AND lk.rw_number = rd.rw_number
        WHERE $where_clause
        ORDER BY lk.tanggal_kegiatan DESC, lk.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $kegiatan_list = $stmt->fetchAll();
    
    // Get RT list for filter
    $rt_stmt = $pdo->query("SELECT DISTINCT rt_number FROM rt_data ORDER BY rt_number");
    $rt_options = $rt_stmt->fetchAll();
    
} catch (Exception $e) {
    $kegiatan_list = [];
    $rt_options = [];
    $total_records = 0;
    $total_pages = 0;
}
?>

<div class="container my-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <h2 class="fw-bold text-primary mb-3">
                    <i class="fas fa-calendar-alt"></i> Kegiatan RT Kelurahan Margasari
                </h2>
                <p class="text-muted">
                    Dokumentasi kegiatan RT di Kelurahan Margasari, Balikpapan Barat
                </p>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <form method="GET" class="card p-3">
                <input type="hidden" name="page" value="kegiatan">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Cari kegiatan...">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
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
                    
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Count -->
    <?php if (!empty($search) || !empty($rt_filter)): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Menampilkan <?php echo $total_records; ?> kegiatan
                    <?php if (!empty($search)): ?>
                        dengan kata kunci "<strong><?php echo htmlspecialchars($search); ?></strong>"
                    <?php endif; ?>
                    <?php if (!empty($rt_filter)): ?>
                        dari RT <?php echo htmlspecialchars($rt_filter); ?>
                    <?php endif; ?>
                    
                    <a href="?page=kegiatan" class="btn btn-outline-info btn-sm ms-2">
                        <i class="fas fa-times"></i> Reset Filter
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Kegiatan List -->
    <?php if (count($kegiatan_list) > 0): ?>
        <div class="row g-4">
            <?php foreach ($kegiatan_list as $kegiatan): ?>
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <?php if ($kegiatan['foto_dokumentasi']): ?>
                            <img src="uploads/activities/<?php echo htmlspecialchars($kegiatan['foto_dokumentasi']); ?>" 
                                 class="card-img-top" alt="Dokumentasi Kegiatan" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" 
                                 style="height: 200px;">
                                <i class="fas fa-calendar-alt fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="badge bg-primary me-2">
                                    RT <?php echo $kegiatan['rt_number']; ?>/RW <?php echo $kegiatan['rw_number']; ?>
                                </span>
                                <span class="badge bg-secondary">
                                    <?php echo format_date($kegiatan['tanggal_kegiatan']); ?>
                                </span>
                            </div>
                            
                            <h5 class="card-title text-primary">
                                <?php echo htmlspecialchars($kegiatan['judul_kegiatan']); ?>
                            </h5>
                            
                            <p class="card-text text-muted">
                                <?php echo substr(htmlspecialchars($kegiatan['deskripsi_kegiatan']), 0, 150); ?>
                                <?php if (strlen($kegiatan['deskripsi_kegiatan']) > 150): ?>...<?php endif; ?>
                            </p>
                            
                            <div class="row text-sm mb-3">
                                <?php if ($kegiatan['tempat_kegiatan']): ?>
                                    <div class="col-12 mb-1">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?php echo htmlspecialchars($kegiatan['tempat_kegiatan']); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($kegiatan['waktu_kegiatan']): ?>
                                    <div class="col-12 mb-1">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> 
                                            <?php echo date('H:i', strtotime($kegiatan['waktu_kegiatan'])); ?> WIB
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($kegiatan['jumlah_peserta']): ?>
                                    <div class="col-12 mb-1">
                                        <small class="text-muted">
                                            <i class="fas fa-users"></i> 
                                            <?php echo $kegiatan['jumlah_peserta']; ?> peserta
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($kegiatan['penanggung_jawab']): ?>
                                    <div class="col-12 mb-1">
                                        <small class="text-muted">
                                            <i class="fas fa-user-tie"></i> 
                                            <?php echo htmlspecialchars($kegiatan['penanggung_jawab']); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?php if ($kegiatan['ketua_rt']): ?>
                                        Ketua RT: <?php echo htmlspecialchars($kegiatan['ketua_rt']); ?>
                                    <?php else: ?>
                                        RT <?php echo $kegiatan['rt_number']; ?>/RW <?php echo $kegiatan['rw_number']; ?>
                                    <?php endif; ?>
                                </small>
                                
                                <?php if ($kegiatan['foto_dokumentasi']): ?>
                                    <a href="uploads/activities/<?php echo htmlspecialchars($kegiatan['foto_dokumentasi']); ?>" 
                                       target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> Lihat Foto
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav class="mt-5" aria-label="Halaman kegiatan">
                <ul class="pagination justify-content-center">
                    <?php if ($page_num > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=kegiatan&p=<?php echo ($page_num - 1); ?>&search=<?php echo urlencode($search); ?>&rt=<?php echo urlencode($rt_filter); ?>">
                                <i class="fas fa-chevron-left"></i> Sebelumnya
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page_num - 2);
                    $end_page = min($total_pages, $page_num + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?php echo ($i == $page_num) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=kegiatan&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&rt=<?php echo urlencode($rt_filter); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page_num < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=kegiatan&p=<?php echo ($page_num + 1); ?>&search=<?php echo urlencode($search); ?>&rt=<?php echo urlencode($rt_filter); ?>">
                                Selanjutnya <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <!-- No Results -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-calendar-times fa-4x text-muted"></i>
            </div>
            <h4 class="text-muted">Tidak Ada Kegiatan</h4>
            <p class="text-muted">
                <?php if (!empty($search) || !empty($rt_filter)): ?>
                    Tidak ditemukan kegiatan yang sesuai dengan filter yang diterapkan.
                <?php else: ?>
                    Belum ada kegiatan RT yang dipublikasikan.
                <?php endif; ?>
            </p>
            
            <?php if (!empty($search) || !empty($rt_filter)): ?>
                <a href="?page=kegiatan" class="btn btn-primary">
                    <i class="fas fa-refresh"></i> Lihat Semua Kegiatan
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Information Card -->
    <div class="row mt-5">
        <div class="col-md-8 mx-auto">
            <div class="card bg-light border-0">
                <div class="card-body text-center">
                    <h5 class="card-title text-primary">
                        <i class="fas fa-info-circle"></i> Informasi Kegiatan RT
                    </h5>
                    <p class="card-text text-muted">
                        Setiap RT di Kelurahan Margasari diharapkan melaporkan minimal 5 kegiatan per bulan. 
                        Dokumentasi kegiatan akan ditampilkan di website ini untuk transparansi kepada masyarakat.
                    </p>
                    <p class="card-text">
                        <small class="text-muted">
                            Untuk informasi lebih lanjut hubungi kantor kelurahan di (0542) 123-456
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
