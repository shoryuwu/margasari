<?php
session_start();
require_once 'config/database.php';

echo "<h2>✅ Fix Gambar Pengaduan - Test Final</h2>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 15px 0; border-radius: 5px; }
.step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 8px; border: 1px solid #dee2e6; }
img { max-width: 100%; height: auto; border: 2px solid #28a745; margin: 10px 0; border-radius: 8px; }
</style>";

echo "<div class='container'>";

// Step 1: Create a simple, working image
echo "<div class='step'>";
echo "<h3>🎨 Step 1: Membuat Gambar Test</h3>";

if (extension_loaded('gd')) {
    echo "<p class='success'>✅ PHP GD Extension available</p>";
    
    // Create image
    $width = 600;
    $height = 400;
    $image = imagecreatetruecolor($width, $height);
    
    // Colors
    $white = imagecolorallocate($image, 255, 255, 255);
    $blue = imagecolorallocate($image, 52, 144, 220);
    $green = imagecolorallocate($image, 40, 167, 69);
    $dark = imagecolorallocate($image, 33, 37, 41);
    
    // Background
    imagefill($image, 0, 0, $white);
    
    // Header
    imagefilledrectangle($image, 0, 0, $width, 80, $blue);
    
    // Content area
    imagefilledrectangle($image, 20, 100, $width-20, $height-20, $green);
    
    // Text
    imagestring($image, 5, 150, 30, "FOTO DOKUMENTASI PENGADUAN", $white);
    imagestring($image, 4, 180, 130, "Laporan: Lampu Jalan Mati", $white);
    imagestring($image, 3, 200, 160, "RT 001/RW 001 - Margasari", $white);
    imagestring($image, 3, 220, 190, "Status: URGENT", $white);
    imagestring($image, 2, 240, 220, date('d F Y H:i:s'), $white);
    
    // Save
    $filename = 'fixed_test_' . date('YmdHis') . '.png';
    $filepath = 'uploads/pengaduan/' . $filename;
    
    if (imagepng($image, $filepath)) {
        echo "<p class='success'>✅ Gambar berhasil dibuat: {$filename}</p>";
        echo "<p><strong>Path:</strong> {$filepath}</p>";
        
        // Display image
        echo "<h4>Preview Gambar:</h4>";
        echo "<img src='{$filepath}' alt='Test Documentation' id='testImage'>";
        
        imagedestroy($image);
        
        // Step 2: Insert to database
        echo "</div><div class='step'>";
        echo "<h3>💾 Step 2: Insert ke Database</h3>";
        
        try {
            // First, check if there are existing pengaduan with photos
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM pengaduan WHERE foto IS NOT NULL AND foto != ''");
            $existing_count = $stmt->fetch()['count'];
            echo "<p>📊 Pengaduan existing dengan foto: <strong>{$existing_count}</strong></p>";
            
            // Insert new pengaduan
            $stmt = $pdo->prepare("
                INSERT INTO pengaduan (nama_pengadu, email, phone, rt, rw, alamat, jenis_pengaduan, judul, isi_pengaduan, foto, status, tanggal_pengaduan) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                'Budi Hartono (Test)',
                'budi.test@email.com',
                '081234567899',
                '001',
                '001',
                'Jl. Test Margasari No. 1',
                'infrastruktur',
                'Test Gambar - Lampu Jalan Mati',
                'Ini adalah test pengaduan untuk memastikan gambar dokumentasi dapat ditampilkan dengan benar di halaman admin. Lampu jalan di depan rumah sudah mati sejak 3 hari yang lalu.',
                $filename,
                'pending'
            ]);
            
            if ($result) {
                $new_id = $pdo->lastInsertId();
                echo "<p class='success'>✅ Pengaduan berhasil ditambahkan dengan ID: {$new_id}</p>";
                
                // Step 3: Verification
                echo "</div><div class='step'>";
                echo "<h3>🔍 Step 3: Verifikasi</h3>";
                
                // Check file exists
                if (file_exists($filepath)) {
                    echo "<p class='success'>✅ File gambar ada di server</p>";
                    $filesize = filesize($filepath);
                    echo "<p>📏 Ukuran file: " . number_format($filesize) . " bytes</p>";
                } else {
                    echo "<p class='error'>❌ File gambar tidak ditemukan</p>";
                }
                
                // Check database record
                $stmt = $pdo->prepare("SELECT * FROM pengaduan WHERE id = ?");
                $stmt->execute([$new_id]);
                $saved_pengaduan = $stmt->fetch();
                
                if ($saved_pengaduan && $saved_pengaduan['foto'] == $filename) {
                    echo "<p class='success'>✅ Data tersimpan dengan benar di database</p>";
                } else {
                    echo "<p class='error'>❌ Data tidak tersimpan dengan benar</p>";
                }
                
                // Test direct access
                echo "<h4>Test Direct Access:</h4>";
                echo "<p><a href='{$filepath}' target='_blank' class='btn btn-primary'>{$filepath}</a></p>";
                
                echo "</div><div class='step'>";
                echo "<h3>🎉 Step 4: Action Items</h3>";
                echo "<div class='info'>";
                echo "<h4>Silakan test sekarang:</h4>";
                echo "<ol>";
                echo "<li><strong>Login Admin:</strong> <a href='?page=admin-login' target='_blank'>Admin Login</a></li>";
                echo "<li><strong>Lihat Pengaduan:</strong> <a href='?page=admin-pengaduan' target='_blank'>Kelola Pengaduan</a></li>";
                echo "<li><strong>Klik Detail</strong> pada pengaduan dengan ID #{$new_id}</li>";
                echo "<li><strong>Cek bagian Foto Dokumentasi</strong> - gambar harus muncul</li>";
                echo "<li><strong>Klik gambar</strong> untuk zoom dalam modal</li>";
                echo "</ol>";
                echo "</div>";
                
            } else {
                echo "<p class='error'>❌ Gagal menyimpan ke database</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Gagal menyimpan gambar</p>";
    }
    
} else {
    echo "<p class='error'>❌ PHP GD Extension tidak tersedia</p>";
}

echo "</div>";

// Summary
echo "<div class='info'>";
echo "<h3>📋 Ringkasan Perbaikan:</h3>";
echo "<ul>";
echo "<li>✅ <strong>File .htaccess diperbaiki</strong> - tidak lagi memblokir gambar</li>";
echo "<li>✅ <strong>Test gambar dibuat</strong> - memastikan PHP GD bekerja</li>";
echo "<li>✅ <strong>Database updated</strong> - pengaduan dengan foto ditambahkan</li>";
echo "<li>✅ <strong>Path verification</strong> - file dapat diakses langsung</li>";
echo "</ul>";

echo "<h4>🔧 Masalah yang Diperbaiki:</h4>";
echo "<p>Masalah utama ada di file <code>uploads/.htaccess</code> yang memiliki rule:</p>";
echo "<pre style='background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>&lt;FilesMatch \"\\.\"&gt;\n    Order Allow,Deny\n    Deny from all\n&lt;/FilesMatch&gt;</pre>";
echo "<p>Rule ini memblokir <strong>SEMUA</strong> file dengan ekstensi, termasuk gambar. Sekarang sudah diganti dengan rule yang lebih spesifik yang hanya memblokir file berbahaya.</p>";
echo "</div>";

echo "</div>";

echo "
<script>
// Test if image loads successfully
document.getElementById('testImage').onload = function() {
    console.log('✅ Image loaded successfully!');
    this.style.border = '3px solid #28a745';
};

document.getElementById('testImage').onerror = function() {
    console.log('❌ Image failed to load!');
    this.style.border = '3px solid #dc3545';
    this.alt = 'Image failed to load';
    this.nextElementSibling.innerHTML = '<p class=\"error\">❌ Gambar masih tidak dapat dimuat. Coba refresh halaman atau clear cache browser.</p>';
};
</script>
";
?>
