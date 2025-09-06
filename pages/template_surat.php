<?php
try {
    // Get template surat yang aktif
    $stmt = $pdo->query("
        SELECT * FROM template_surat 
        WHERE is_active = 1 
        ORDER BY jenis_surat, nama_template
    ");
    $templates = $stmt->fetchAll();
    
} catch (Exception $e) {
    $templates = [];
}
?>

<div class="container my-5">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="text-center">
                <h2 class="fw-bold text-primary mb-3">
                    <i class="fas fa-file-contract"></i> Template Surat Kelurahan Margasari
                </h2>
                <p class="text-muted">
                    Kumpulan template surat keterangan yang tersedia di Kelurahan Margasari
                </p>
            </div>
        </div>
    </div>

    <?php if (count($templates) > 0): ?>
        <!-- Template Categories -->
        <div class="row g-4">
            <?php
            $jenis_surat_map = [
                'domisili' => ['name' => 'Surat Keterangan Domisili', 'icon' => 'fa-home', 'color' => 'primary'],
                'usaha' => ['name' => 'Surat Keterangan Usaha', 'icon' => 'fa-store', 'color' => 'success'],
                'tidak_mampu' => ['name' => 'Surat Keterangan Tidak Mampu', 'icon' => 'fa-hand-holding-heart', 'color' => 'warning'],
                'berkelakuan_baik' => ['name' => 'Surat Berkelakuan Baik', 'icon' => 'fa-medal', 'color' => 'info'],
                'lainnya' => ['name' => 'Surat Lainnya', 'icon' => 'fa-file-alt', 'color' => 'secondary']
            ];
            
            $templates_by_type = [];
            foreach ($templates as $template) {
                $templates_by_type[$template['jenis_surat']][] = $template;
            }
            
            foreach ($templates_by_type as $jenis => $template_list):
                $jenis_info = $jenis_surat_map[$jenis] ?? $jenis_surat_map['lainnya'];
            ?>
            
            <div class="col-md-6">
                <div class="card h-100 border-<?php echo $jenis_info['color']; ?>">
                    <div class="card-header bg-<?php echo $jenis_info['color']; ?> text-white">
                        <h5 class="mb-0">
                            <i class="fas <?php echo $jenis_info['icon']; ?>"></i> 
                            <?php echo $jenis_info['name']; ?>
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <?php foreach ($template_list as $template): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="text-<?php echo $jenis_info['color']; ?> mb-2">
                                    <?php echo htmlspecialchars($template['nama_template']); ?>
                                </h6>
                                
                                <!-- Template Fields -->
                                <?php if ($template['template_fields']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted"><strong>Field yang diperlukan:</strong></small>
                                        <div class="mt-1">
                                            <?php
                                            $fields = json_decode($template['template_fields'], true);
                                            if (is_array($fields)) {
                                                foreach ($fields as $field) {
                                                    echo '<span class="badge bg-light text-dark me-1 mb-1">' . htmlspecialchars($field) . '</span>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Template Preview (first 200 chars) -->
                                <div class="bg-light p-3 rounded mb-3" style="font-size: 0.9em;">
                                    <?php 
                                    $preview = substr($template['template_content'], 0, 200);
                                    echo '<pre style="white-space: pre-wrap; margin: 0;">' . htmlspecialchars($preview) . '...</pre>';
                                    ?>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-<?php echo $jenis_info['color']; ?> btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#templateModal<?php echo $template['id']; ?>">
                                        <i class="fas fa-eye"></i> Lihat Template
                                    </button>
                                    
                                    <a href="?page=permintaan-surat&template=<?php echo $template['id']; ?>" 
                                       class="btn btn-<?php echo $jenis_info['color']; ?> btn-sm">
                                        <i class="fas fa-file-plus"></i> Ajukan Permohonan
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <?php endforeach; ?>
        </div>

        <!-- Template Modals -->
        <?php foreach ($templates as $template): ?>
            <div class="modal fade" id="templateModal<?php echo $template['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-file-contract"></i> 
                                <?php echo htmlspecialchars($template['nama_template']); ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="bg-light p-4 rounded">
                                <pre style="white-space: pre-wrap; font-family: 'Times New Roman', serif; line-height: 1.6;">
<?php echo htmlspecialchars($template['template_content']); ?>
                                </pre>
                            </div>
                            
                            <?php if ($template['template_fields']): ?>
                                <div class="mt-4">
                                    <h6>Field yang akan diisi:</h6>
                                    <?php
                                    $fields = json_decode($template['template_fields'], true);
                                    if (is_array($fields)) {
                                        echo '<ul class="list-unstyled">';
                                        foreach ($fields as $field) {
                                            echo '<li><span class="badge bg-primary me-2">[' . htmlspecialchars($field) . ']</span> ' . ucfirst(str_replace('_', ' ', $field)) . '</li>';
                                        }
                                        echo '</ul>';
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <a href="?page=permintaan-surat&template=<?php echo $template['id']; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-file-plus"></i> Ajukan Permohonan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <!-- No Templates -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-file-contract fa-4x text-muted"></i>
            </div>
            <h4 class="text-muted">Template Belum Tersedia</h4>
            <p class="text-muted">Template surat sedang dalam proses pengembangan.</p>
        </div>
    <?php endif; ?>

    <!-- Information Cards -->
    <div class="row mt-5">
        <div class="col-md-6">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i> Cara Mengajukan Surat
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="list-unstyled">
                        <li class="mb-2">
                            <span class="badge bg-info rounded-circle me-2">1</span>
                            Pilih template surat yang sesuai
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-info rounded-circle me-2">2</span>
                            Klik "Ajukan Permohonan"
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-info rounded-circle me-2">3</span>
                            Isi formulir dengan data lengkap
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-info rounded-circle me-2">4</span>
                            Tunggu konfirmasi dari staff kelurahan
                        </li>
                        <li class="mb-0">
                            <span class="badge bg-info rounded-circle me-2">5</span>
                            Ambil surat di kantor kelurahan
                        </li>
                    </ol>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-warning">
                <div class="card-header bg-warning">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Syarat dan Ketentuan
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Data yang diisi harus benar dan valid
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Melampirkan dokumen pendukung jika diperlukan
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Proses verifikasi 1-3 hari kerja
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Pengambilan surat sesuai jadwal yang ditentukan
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            Layanan gratis untuk warga Kelurahan Margasari
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5>
                        <i class="fas fa-phone"></i> Butuh Bantuan?
                    </h5>
                    <p class="mb-0">
                        Hubungi kami di (0542) 123-456 atau datang langsung ke kantor kelurahan<br>
                        <small>Senin - Jumat, 08:00 - 16:00 WIB</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
