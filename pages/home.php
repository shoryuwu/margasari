<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Selamat Datang di Website</h1>
            <h2 class="hero-subtitle">Kelurahan Margasari</h2>
            <p class="hero-subtitle">Melayani Masyarakat dengan Transparansi dan Responsif</p>
            <a href="?page=pengaduan" class="btn btn-light btn-lg">
                <i class="fas fa-comment-dots"></i> Sampaikan Pengaduan
            </a>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="container my-5">
    <div class="row text-center mb-5">
        <div class="col-12">
            <h2 class="fw-bold text-primary">Layanan Kami</h2>
            <p class="text-muted">Berbagai layanan yang dapat membantu kebutuhan masyarakat</p>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card service-card">
                <div class="service-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <h4 class="service-title">Pengaduan Online</h4>
                <p class="service-description">Sampaikan keluhan dan saran Anda secara online dengan mudah dan cepat.</p>
                <a href="?page=pengaduan" class="btn btn-primary">Buka Layanan</a>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card service-card">
                <div class="service-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h4 class="service-title">Permintaan Surat</h4>
                <p class="service-description">Ajukan permohonan surat keterangan secara online tanpa antri.</p>
                <a href="?page=permintaan-surat" class="btn btn-primary">Buka Layanan</a>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card service-card">
                <div class="service-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h4 class="service-title">Kegiatan RT</h4>
                <p class="service-description">Lihat laporan kegiatan RT terbaru dan dokumentasinya.</p>
                <a href="?page=kegiatan" class="btn btn-primary">Lihat Kegiatan</a>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card service-card">
                <div class="service-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h4 class="service-title">Peta Wilayah</h4>
                <p class="service-description">Jelajahi peta kelurahan dan data kependudukan per RT.</p>
                <a href="?page=peta" class="btn btn-primary">Lihat Peta</a>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="row text-center mb-4">
            <div class="col-12">
                <h2 class="fw-bold text-primary">Data Kelurahan</h2>
                <p class="text-muted">Statistik terkini Kelurahan Margasari</p>
            </div>
        </div>
        
        <div class="row">
            <?php
            try {
                // Get total RT
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM rt_data");
                $total_rt = $stmt->fetch()['total'];
                
                // Get total KK
                $stmt = $pdo->query("SELECT SUM(total_kk) as total FROM rt_data");
                $total_kk = $stmt->fetch()['total'] ?? 0;
                
                // Get total penduduk
                $stmt = $pdo->query("SELECT SUM(total_penduduk) as total FROM rt_data");
                $total_penduduk = $stmt->fetch()['total'] ?? 0;
                
                // Get total pengaduan bulan ini
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengaduan WHERE MONTH(tanggal_pengaduan) = MONTH(CURRENT_DATE())");
                $pengaduan_bulan_ini = $stmt->fetch()['total'];
            } catch (Exception $e) {
                $total_rt = 0;
                $total_kk = 0;
                $total_penduduk = 0;
                $pengaduan_bulan_ini = 0;
            }
            ?>
            
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_rt; ?></div>
                    <div class="stat-label">Total RT</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($total_kk); ?></div>
                    <div class="stat-label">Kepala Keluarga</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($total_penduduk); ?></div>
                    <div class="stat-label">Total Penduduk</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $pengaduan_bulan_ini; ?></div>
                    <div class="stat-label">Pengaduan Bulan Ini</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Activities -->
<section class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-primary">Kegiatan Terbaru</h2>
            <p class="text-muted">Dokumentasi kegiatan RT terbaru di Kelurahan Margasari</p>
        </div>
    </div>
    
    <div class="row g-4">
        <?php
        try {
            $stmt = $pdo->query("
                SELECT lk.*, rd.ketua_rt 
                FROM laporan_kegiatan lk 
                LEFT JOIN rt_data rd ON lk.rt_number = rd.rt_number AND lk.rw_number = rd.rw_number
                WHERE lk.status = 'published' 
                ORDER BY lk.tanggal_kegiatan DESC 
                LIMIT 3
            ");
            $kegiatan = $stmt->fetchAll();
            
            if (count($kegiatan) > 0) {
                foreach ($kegiatan as $activity) {
                    echo '<div class="col-md-4">';
                    echo '  <div class="card activity-card">';
                    if ($activity['foto_dokumentasi']) {
                        echo '    <img src="uploads/activities/' . $activity['foto_dokumentasi'] . '" class="card-img-top" alt="Dokumentasi Kegiatan">';
                    } else {
                        echo '    <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">';
                        echo '      <i class="fas fa-calendar-alt fa-3x text-muted"></i>';
                        echo '    </div>';
                    }
                    echo '    <div class="card-body">';
                    echo '      <span class="activity-date">' . format_date($activity['tanggal_kegiatan']) . '</span>';
                    echo '      <h5 class="card-title mt-2">' . htmlspecialchars($activity['judul_kegiatan']) . '</h5>';
                    echo '      <p class="card-text">' . substr(htmlspecialchars($activity['deskripsi_kegiatan']), 0, 100) . '...</p>';
                    echo '      <small class="text-muted">';
                    echo '        <i class="fas fa-map-marker-alt"></i> RT ' . $activity['rt_number'] . '/RW ' . $activity['rw_number'];
                    echo '      </small>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12 text-center">';
                echo '  <div class="alert alert-info">';
                echo '    <i class="fas fa-info-circle"></i> Belum ada kegiatan yang dipublikasikan.';
                echo '  </div>';
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<div class="col-12">';
            echo '  <div class="alert alert-warning">';
            echo '    <i class="fas fa-exclamation-triangle"></i> Data kegiatan belum tersedia.';
            echo '  </div>';
            echo '</div>';
        }
        ?>
    </div>
    
    <div class="text-center mt-4">
        <a href="?page=kegiatan" class="btn btn-outline-primary">
            <i class="fas fa-calendar-alt"></i> Lihat Semua Kegiatan
        </a>
    </div>
</section>

<!-- News/Announcements -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold text-primary">Pengumuman Terbaru</h2>
                <p class="text-muted">Informasi penting untuk warga Kelurahan Margasari</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="fas fa-bullhorn"></i> Jadwal Pelayanan
                        </h5>
                        <p class="card-text">
                            Pelayanan administrasi dilaksanakan setiap hari kerja, Senin-Jumat pukul 08:00-16:00 WIB. 
                            Untuk permintaan surat dapat diajukan secara online melalui website ini.
                        </p>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Diperbarui: <?php echo format_date(date('Y-m-d')); ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="fas fa-hands-helping"></i> Program Bantuan Sosial
                        </h5>
                        <p class="card-text">
                            Pendataan penerima bantuan sosial sedang berlangsung. Warga yang membutuhkan dapat 
                            menghubungi RT setempat atau datang langsung ke kantor kelurahan.
                        </p>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Diperbarui: <?php echo format_date(date('Y-m-d')); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
