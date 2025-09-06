<?php
$success = false;
$error = '';

if ($_POST) {
    $nama_pengadu = sanitize_input($_POST['nama_pengadu'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $rt = sanitize_input($_POST['rt'] ?? '');
    $rw = sanitize_input($_POST['rw'] ?? '');
    $alamat = sanitize_input($_POST['alamat'] ?? '');
    $jenis_pengaduan = sanitize_input($_POST['jenis_pengaduan'] ?? '');
    $judul = sanitize_input($_POST['judul'] ?? '');
    $isi_pengaduan = sanitize_input($_POST['isi_pengaduan'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Token keamanan tidak valid';
    } else if (empty($nama_pengadu) || empty($phone) || empty($jenis_pengaduan) || empty($judul) || empty($isi_pengaduan)) {
        $error = 'Semua field yang wajib harus diisi';
    } else {
        try {
            $foto = null;
            
            // Handle file upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $upload_result = upload_file($_FILES['foto'], 'uploads/pengaduan', ['jpg', 'jpeg', 'png']);
                if ($upload_result['success']) {
                    $foto = $upload_result['filename'];
                } else {
                    $error = $upload_result['message'];
                }
            }
            
            if (empty($error)) {
                $stmt = $pdo->prepare("
                    INSERT INTO pengaduan (nama_pengadu, email, phone, rt, rw, alamat, jenis_pengaduan, judul, isi_pengaduan, foto) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $nama_pengadu, $email, $phone, $rt, $rw, $alamat, 
                    $jenis_pengaduan, $judul, $isi_pengaduan, $foto
                ]);
                
                $success = true;
                
                // Reset form data
                $_POST = [];
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-comment-dots"></i> Pengaduan Masyarakat
                    </h3>
                    <small>Sampaikan keluhan, saran, atau laporan Anda kepada Kelurahan Margasari</small>
                </div>
                
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i>
                            <strong>Pengaduan Berhasil Dikirim!</strong><br>
                            Terima kasih atas pengaduan Anda. Tim kami akan segera menindaklanjuti dan memberikan respon dalam 1x24 jam.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        
                        <div class="text-center">
                            <a href="?page=pengaduan" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Buat Pengaduan Baru
                            </a>
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-home"></i> Kembali ke Beranda
                            </a>
                        </div>
                    <?php else: ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data" id="pengaduanForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama_pengadu" class="form-label">
                                        Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nama_pengadu" name="nama_pengadu" 
                                           value="<?php echo htmlspecialchars($_POST['nama_pengadu'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        Nomor HP <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" class="form-control phone-input" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                           placeholder="081234567890" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       placeholder="email@example.com">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="rt" class="form-label">RT</label>
                                    <input type="text" class="form-control" id="rt" name="rt" 
                                           value="<?php echo htmlspecialchars($_POST['rt'] ?? ''); ?>"
                                           placeholder="001">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="rw" class="form-label">RW</label>
                                    <input type="text" class="form-control" id="rw" name="rw" 
                                           value="<?php echo htmlspecialchars($_POST['rw'] ?? ''); ?>"
                                           placeholder="001">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="jenis_pengaduan" class="form-label">
                                        Jenis Pengaduan <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="jenis_pengaduan" name="jenis_pengaduan" required>
                                        <option value="">Pilih Jenis Pengaduan</option>
                                        <option value="infrastruktur" <?php echo ($_POST['jenis_pengaduan'] ?? '') == 'infrastruktur' ? 'selected' : ''; ?>>
                                            Infrastruktur (Jalan, Drainase, dll)
                                        </option>
                                        <option value="pelayanan" <?php echo ($_POST['jenis_pengaduan'] ?? '') == 'pelayanan' ? 'selected' : ''; ?>>
                                            Pelayanan Publik
                                        </option>
                                        <option value="kebersihan" <?php echo ($_POST['jenis_pengaduan'] ?? '') == 'kebersihan' ? 'selected' : ''; ?>>
                                            Kebersihan & Lingkungan
                                        </option>
                                        <option value="keamanan" <?php echo ($_POST['jenis_pengaduan'] ?? '') == 'keamanan' ? 'selected' : ''; ?>>
                                            Keamanan & Ketertiban
                                        </option>
                                        <option value="lainnya" <?php echo ($_POST['jenis_pengaduan'] ?? '') == 'lainnya' ? 'selected' : ''; ?>>
                                            Lainnya
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control auto-resize" id="alamat" name="alamat" rows="2" 
                                          placeholder="Alamat lengkap lokasi pengaduan atau alamat pengadu"><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="judul" class="form-label">
                                    Judul Pengaduan <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="judul" name="judul" 
                                       value="<?php echo htmlspecialchars($_POST['judul'] ?? ''); ?>"
                                       placeholder="Ringkasan singkat pengaduan Anda" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="isi_pengaduan" class="form-label">
                                    Detail Pengaduan <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control auto-resize" id="isi_pengaduan" name="isi_pengaduan" rows="4" 
                                          placeholder="Jelaskan detail pengaduan Anda secara lengkap dan jelas..." required><?php echo htmlspecialchars($_POST['isi_pengaduan'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label for="foto" class="form-label">Foto Dokumentasi (Opsional)</label>
                                <input type="file" class="form-control" id="foto" name="foto" accept=".jpg,.jpeg,.png">
                                <div class="form-text">
                                    Format: JPG, JPEG, PNG. Maksimal 5MB. 
                                    Upload foto untuk memperkuat pengaduan Anda.
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" onclick="return validateForm('pengaduanForm')">
                                    <i class="fas fa-paper-plane"></i> Kirim Pengaduan
                                </button>
                            </div>
                        </form>
                        
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Informasi Tambahan -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="card-title text-info">
                                <i class="fas fa-info-circle"></i> Ketentuan Pengaduan
                            </h6>
                            <ul class="list-unstyled small text-muted">
                                <li>• Gunakan bahasa yang sopan dan jelas</li>
                                <li>• Sertakan data yang akurat dan lengkap</li>
                                <li>• Foto dokumentasi akan mempercepat penanganan</li>
                                <li>• Respon akan diberikan dalam 1x24 jam</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="card-title text-success">
                                <i class="fas fa-phone"></i> Kontak Langsung
                            </h6>
                            <p class="small text-muted mb-1">
                                <strong>Telepon:</strong> (0542) 123-456
                            </p>
                            <p class="small text-muted mb-1">
                                <strong>WhatsApp:</strong> 0812-3456-7890
                            </p>
                            <p class="small text-muted mb-0">
                                <strong>Jam Kerja:</strong> Senin-Jumat 08:00-16:00
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
