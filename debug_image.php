<?php
echo "<h2>🔍 Debug Gambar Pengaduan</h2>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { background: #e7f3ff; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
.warning { background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 10px 0; }
img { max-width: 300px; border: 1px solid #ccc; margin: 10px; }
</style>";

// 1. Check folder uploads/pengaduan
echo "<h3>1. Mengecek Folder uploads/pengaduan</h3>";
$upload_dir = 'uploads/pengaduan/';
$full_path = __DIR__ . '/' . $upload_dir;

echo "<p><strong>Path relatif:</strong> {$upload_dir}</p>";
echo "<p><strong>Path absolut:</strong> {$full_path}</p>";

if (is_dir($full_path)) {
    echo "<p class='success'>✅ Folder exist</p>";
    
    // List files
    echo "<h4>File dalam folder:</h4>";
    $files = scandir($full_path);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $file_path = $full_path . $file;
            $file_size = filesize($file_path);
            echo "<li><strong>{$file}</strong> - " . number_format($file_size) . " bytes</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p class='error'>❌ Folder tidak exist</p>";
}

// 2. Test direct image access
echo "<h3>2. Test Akses Gambar Langsung</h3>";
$image_files = glob($full_path . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

if (count($image_files) > 0) {
    foreach ($image_files as $image_file) {
        $relative_path = str_replace(__DIR__ . '/', '', $image_file);
        $filename = basename($image_file);
        
        echo "<div class='info'>";
        echo "<h4>File: {$filename}</h4>";
        echo "<p><strong>Path:</strong> {$relative_path}</p>";
        
        // Test if file is readable
        if (is_readable($image_file)) {
            echo "<p class='success'>✅ File dapat dibaca</p>";
            
            // Get image info
            $image_info = @getimagesize($image_file);
            if ($image_info) {
                echo "<p><strong>Dimensi:</strong> {$image_info[0]}x{$image_info[1]}px</p>";
                echo "<p><strong>MIME:</strong> {$image_info['mime']}</p>";
            } else {
                echo "<p class='error'>❌ File bukan gambar valid</p>";
            }
            
            // Try to display image
            echo "<h5>Preview Gambar:</h5>";
            echo "<img src='{$relative_path}' alt='Test Image' onerror=\"this.style.display='none'; this.nextSibling.style.display='block';\">";
            echo "<div style='display:none; color:red; font-weight:bold;'>❌ Gambar tidak dapat dimuat dari browser</div>";
            
        } else {
            echo "<p class='error'>❌ File tidak dapat dibaca</p>";
        }
        echo "</div>";
    }
} else {
    echo "<p class='warning'>⚠️ Tidak ada file gambar ditemukan</p>";
}

// 3. Check .htaccess
echo "<h3>3. Mengecek .htaccess di folder uploads</h3>";
$htaccess_path = 'uploads/.htaccess';
if (file_exists($htaccess_path)) {
    echo "<p class='info'>📄 File .htaccess ditemukan</p>";
    echo "<h4>Isi file .htaccess:</h4>";
    echo "<pre style='background:#f8f9fa; padding:15px; border:1px solid #ddd;'>";
    echo htmlspecialchars(file_get_contents($htaccess_path));
    echo "</pre>";
    
    // Check if it blocks image access
    $htaccess_content = file_get_contents($htaccess_path);
    if (strpos($htaccess_content, 'php_flag engine off') !== false) {
        echo "<div class='warning'>⚠️ .htaccess menonaktifkan PHP engine - ini bagus untuk keamanan</div>";
    }
    if (strpos($htaccess_content, 'jpg|jpeg|png|gif') !== false) {
        echo "<div class='info'>✅ .htaccess mengizinkan akses gambar (jpg, jpeg, png, gif)</div>";
    } else {
        echo "<div class='error'>❌ .htaccess mungkin memblokir akses gambar</div>";
    }
} else {
    echo "<p class='warning'>⚠️ File .htaccess tidak ditemukan di folder uploads</p>";
}

// 4. Test create simple image
echo "<h3>4. Test Membuat Gambar Sederhana</h3>";
if (extension_loaded('gd')) {
    echo "<p class='success'>✅ PHP GD Extension tersedia</p>";
    
    // Create simple test image
    $test_image = imagecreate(200, 100);
    $bg_color = imagecolorallocate($test_image, 240, 240, 240);
    $text_color = imagecolorallocate($test_image, 0, 0, 0);
    
    imagestring($test_image, 5, 50, 30, "TEST IMAGE", $text_color);
    
    $test_filename = 'test_' . date('His') . '.png';
    $test_path = $upload_dir . $test_filename;
    
    if (imagepng($test_image, $test_path)) {
        echo "<p class='success'>✅ Test image berhasil dibuat: {$test_filename}</p>";
        echo "<img src='{$test_path}' alt='Test Image'>";
    } else {
        echo "<p class='error'>❌ Gagal membuat test image</p>";
    }
    
    imagedestroy($test_image);
} else {
    echo "<p class='error'>❌ PHP GD Extension tidak tersedia</p>";
}

// 5. Check database entries
echo "<h3>5. Mengecek Data Pengaduan di Database</h3>";
try {
    require_once 'config/database.php';
    
    $stmt = $pdo->query("SELECT id, nama_pengadu, judul, foto FROM pengaduan WHERE foto IS NOT NULL AND foto != '' ORDER BY id DESC LIMIT 5");
    $pengaduan_with_photos = $stmt->fetchAll();
    
    if (count($pengaduan_with_photos) > 0) {
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><th>ID</th><th>Nama</th><th>Judul</th><th>Filename</th><th>File Exist?</th></tr>";
        
        foreach ($pengaduan_with_photos as $p) {
            $file_exists = file_exists($upload_dir . $p['foto']);
            $status = $file_exists ? "✅ Yes" : "❌ No";
            $status_class = $file_exists ? "success" : "error";
            
            echo "<tr>";
            echo "<td>{$p['id']}</td>";
            echo "<td>" . htmlspecialchars($p['nama_pengadu']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($p['judul'], 0, 30)) . "...</td>";
            echo "<td>{$p['foto']}</td>";
            echo "<td class='{$status_class}'>{$status}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Tidak ada pengaduan dengan foto di database</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error database: " . $e->getMessage() . "</p>";
}

// 6. Recommendations
echo "<h3>6. Solusi & Rekomendasi</h3>";
echo "<div class='info'>";
echo "<h4>Jika gambar tidak tampil, coba:</h4>";
echo "<ol>";
echo "<li><strong>Akses langsung:</strong> Buka <a href='uploads/pengaduan/' target='_blank'>uploads/pengaduan/</a> di browser</li>";
echo "<li><strong>Cek permission:</strong> Pastikan folder uploads/pengaduan dapat diakses web server</li>";
echo "<li><strong>Cek .htaccess:</strong> Pastikan tidak memblokir akses gambar</li>";
echo "<li><strong>Clear cache:</strong> Refresh browser atau clear cache</li>";
echo "<li><strong>Cek console:</strong> Buka F12 -> Console untuk melihat error</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><a href='?page=admin-pengaduan'>🔙 Kembali ke Admin Pengaduan</a> | <a href='index.php'>🏠 Home</a></p>";
?>
