<?php
$success = false;
$error = '';
$selected_template = null;

// Get template if specified
if (isset($_GET['template'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM template_surat WHERE id = ? AND is_active = 1");
        $stmt->execute([$_GET['template']]);
        $selected_template = $stmt->fetch();
    } catch (Exception $e) {
        $selected_template = null;
    }
}

// Get all active templates for dropdown
try {
    $stmt = $pdo->query("SELECT * FROM template_surat WHERE is_active = 1 ORDER BY jenis_surat, nama_template");
    $all_templates = $stmt->fetchAll();
} catch (Exception $e) {
    $all_templates = [];
}

if ($_POST) {
    $nama_pemohon = sanitize_input($_POST['nama_pemohon'] ?? '');
    $nik = sanitize_input($_POST['nik'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $alamat = sanitize_input($_POST['alamat'] ?? '');
    $rt = sanitize_input($_POST['rt'] ?? '');
    $rw = sanitize_input($_POST['rw'] ?? '');
    $template_id = (int)($_POST['template_id'] ?? 0);
    $keperluan = sanitize_input($_POST['keperluan'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Token keamanan tidak valid';
    } else if (empty($nama_pemohon) || empty($nik) || empty($phone) || empty($alamat) || empty($rt) || empty($rw) || empty($template_id) || empty($keperluan)) {
        $error = 'Semua field yang wajib harus diisi';
    } else {
        try {
            // Get template data
            $stmt = $pdo->prepare("SELECT * FROM template_surat WHERE id = ? AND is_active = 1");
            $stmt->execute([$template_id]);
            $template = $stmt->fetch();
            
            if (!$template) {
                $error = 'Template surat tidak valid';
            } else {
                // Collect template field data
                $data_fields = [];
                $template_fields = json_decode($template['template_fields'], true) ?: [];
                
                foreach ($template_fields as $field) {
                    $field_value = sanitize_input($_POST['field_' . $field] ?? '');
                    if (empty($field_value)) {
                        $error = "Field '$field' harus diisi";
                        break;
                    }
                    $data_fields[$field] = $field_value;
                }
                
                if (empty($error)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO permintaan_surat (nama_pemohon, nik, email, phone, alamat, rt, rw, template_id, data_fields, keperluan) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $nama_pemohon, $nik, $email, $phone, $alamat, 
                        $rt, $rw, $template_id, json_encode($data_fields), $keperluan
                    ]);
                    
                    $success = true;
                    // Reset form data
                    $_POST = [];
                }
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-file-plus"></i> Permintaan Surat Keterangan
                    </h3>
                    <small>Ajukan permohonan surat keterangan secara online</small>
                </div>
                
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i>
                            <strong>Permohonan Berhasil Dikirim!</strong><br>
                            Permohonan surat Anda telah diterima dan akan diproses dalam 1-3 hari kerja. 
                            Staff kelurahan akan menghubungi Anda untuk konfirmasi lebih lanjut.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        
                        <div class="text-center">
                            <a href="?page=permintaan-surat" class="btn btn-success">
                                <i class="fas fa-plus"></i> Buat Permohonan Baru
                            </a>
                            <a href="?page=template-surat" class="btn btn-outline-success">
                                <i class="fas fa-file-contract"></i> Lihat Template Lain
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
                        
                        <form method="POST" id="permintaanSuratForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <!-- Template Selection -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-file-contract text-primary"></i> 
                                        1. Pilih Jenis Surat
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label for="template_id" class="form-label">
                                                Jenis Surat <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="template_id" name="template_id" required onchange="loadTemplateFields(this.value)">
                                                <option value="">Pilih Jenis Surat</option>
                                                <?php
                                                $jenis_surat_map = [
                                                    'domisili' => 'Surat Keterangan Domisili',
                                                    'usaha' => 'Surat Keterangan Usaha',
                                                    'tidak_mampu' => 'Surat Keterangan Tidak Mampu',
                                                    'berkelakuan_baik' => 'Surat Berkelakuan Baik',
                                                    'lainnya' => 'Surat Lainnya'
                                                ];
                                                
                                                foreach ($all_templates as $template):
                                                    $selected = ($selected_template && $selected_template['id'] == $template['id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?php echo $template['id']; ?>" 
                                                            data-fields="<?php echo htmlspecialchars($template['template_fields']); ?>"
                                                            <?php echo $selected; ?>>
                                                        <?php echo htmlspecialchars($template['nama_template']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <a href="?page=template-surat" class="btn btn-outline-info" target="_blank">
                                                    <i class="fas fa-eye"></i> Lihat Template
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Personal Data -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user text-success"></i> 
                                        2. Data Pemohon
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nama_pemohon" class="form-label">
                                                Nama Lengkap <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="nama_pemohon" name="nama_pemohon" 
                                                   value="<?php echo htmlspecialchars($_POST['nama_pemohon'] ?? ''); ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="nik" class="form-label">
                                                NIK <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="nik" name="nik" 
                                                   value="<?php echo htmlspecialchars($_POST['nik'] ?? ''); ?>"
                                                   maxlength="16" pattern="[0-9]{16}" required>
                                            <div class="form-text">16 digit NIK sesuai KTP</div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">
                                                Nomor HP <span class="text-danger">*</span>
                                            </label>
                                            <input type="tel" class="form-control phone-input" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                                   placeholder="081234567890" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                                   placeholder="email@example.com">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="alamat" class="form-label">
                                                Alamat Lengkap <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control auto-resize" id="alamat" name="alamat" rows="2" 
                                                      placeholder="Alamat lengkap sesuai KTP" required><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <div class="col-md-2 mb-3">
                                            <label for="rt" class="form-label">
                                                RT <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="rt" name="rt" 
                                                   value="<?php echo htmlspecialchars($_POST['rt'] ?? ''); ?>"
                                                   placeholder="001" maxlength="3" required>
                                        </div>
                                        
                                        <div class="col-md-2 mb-3">
                                            <label for="rw" class="form-label">
                                                RW <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="rw" name="rw" 
                                                   value="<?php echo htmlspecialchars($_POST['rw'] ?? ''); ?>"
                                                   placeholder="001" maxlength="3" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Template Fields (Dynamic) -->
                            <div class="card mb-4" id="templateFieldsCard" style="display: none;">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-edit text-warning"></i> 
                                        3. Data untuk Surat
                                    </h5>
                                </div>
                                <div class="card-body" id="templateFieldsContainer">
                                    <!-- Dynamic fields will be loaded here -->
                                </div>
                            </div>
                            
                            <!-- Keperluan -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clipboard-list text-info"></i> 
                                        4. Keperluan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="keperluan" class="form-label">
                                            Keperluan Surat <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control auto-resize" id="keperluan" name="keperluan" rows="3" 
                                                  placeholder="Jelaskan untuk keperluan apa surat ini akan digunakan..." required><?php echo htmlspecialchars($_POST['keperluan'] ?? ''); ?></textarea>
                                        <div class="form-text">Contoh: Untuk keperluan daftar sekolah, melamar pekerjaan, dll.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg" onclick="return validateForm('permintaanSuratForm')">
                                    <i class="fas fa-paper-plane"></i> Kirim Permohonan
                                </button>
                            </div>
                        </form>
                        
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Informasi Tambahan -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="card-title text-success">
                                <i class="fas fa-clock"></i> Waktu Proses
                            </h6>
                            <ul class="list-unstyled small text-muted">
                                <li>• Verifikasi data: 1 hari kerja</li>
                                <li>• Proses pembuatan: 1-2 hari kerja</li>
                                <li>• Total maksimal: 3 hari kerja</li>
                                <li>• Anda akan dihubungi jika ada kekurangan data</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="card-title text-info">
                                <i class="fas fa-file-medical"></i> Dokumen Pendukung
                            </h6>
                            <p class="small text-muted">
                                Siapkan dokumen berikut untuk mempercepat proses verifikasi:
                            </p>
                            <ul class="list-unstyled small text-muted">
                                <li>• Fotokopi KTP yang masih berlaku</li>
                                <li>• Fotokopi Kartu Keluarga</li>
                                <li>• Dokumen pendukung lain (jika diperlukan)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadTemplateFields(templateId) {
    const fieldsCard = document.getElementById('templateFieldsCard');
    const fieldsContainer = document.getElementById('templateFieldsContainer');
    
    if (!templateId) {
        fieldsCard.style.display = 'none';
        return;
    }
    
    // Get selected template data
    const templateSelect = document.getElementById('template_id');
    const selectedOption = templateSelect.options[templateSelect.selectedIndex];
    const fieldsData = selectedOption.getAttribute('data-fields');
    
    if (!fieldsData) {
        fieldsCard.style.display = 'none';
        return;
    }
    
    try {
        const fields = JSON.parse(fieldsData);
        
        if (fields && fields.length > 0) {
            let html = '<div class="row">';
            
            fields.forEach((field, index) => {
                const fieldLabel = field.charAt(0).toUpperCase() + field.slice(1).replace(/_/g, ' ');
                const fieldName = 'field_' + field;
                
                html += `
                    <div class="col-md-6 mb-3">
                        <label for="${fieldName}" class="form-label">
                            ${fieldLabel} <span class="text-danger">*</span>
                        </label>`;
                
                if (field === 'jenis_kelamin') {
                    html += `
                        <select class="form-select" id="${fieldName}" name="${fieldName}" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>`;
                } else if (field === 'tanggal_lahir') {
                    html += `
                        <input type="date" class="form-control" id="${fieldName}" name="${fieldName}" required>`;
                } else {
                    html += `
                        <input type="text" class="form-control" id="${fieldName}" name="${fieldName}" 
                               placeholder="Masukkan ${fieldLabel.toLowerCase()}" required>`;
                }
                
                html += '</div>';
                
                if ((index + 1) % 2 === 0) {
                    html += '</div><div class="row">';
                }
            });
            
            html += '</div>';
            fieldsContainer.innerHTML = html;
            fieldsCard.style.display = 'block';
        } else {
            fieldsCard.style.display = 'none';
        }
    } catch (e) {
        console.error('Error parsing template fields:', e);
        fieldsCard.style.display = 'none';
    }
}

// Load template fields if template is pre-selected
document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('template_id');
    if (templateSelect.value) {
        loadTemplateFields(templateSelect.value);
    }
});
</script>
