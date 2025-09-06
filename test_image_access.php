<?php
echo "<h2>🧪 Test Akses Gambar Setelah Perbaikan .htaccess</h2>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { background: #e7f3ff; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
img { max-width: 400px; border: 2px solid #28a745; margin: 10px; border-radius: 8px; }
</style>";

// Create a simple test image
echo "<h3>1. Membuat Test Image...</h3>";
$width = 400;
$height = 200;
$image = imagecreatetruecolor($width, $height);

// Colors
$bg_color = imagecolorallocate($image, 135, 206, 235); // Sky blue
$text_color = imagecolorallocate($image, 25, 25, 112); // Midnight blue
$border_color = imagecolorallocate($image, 0, 100, 0); // Dark green

// Fill background
imagefill($image, 0, 0, $bg_color);

// Draw border
imagerectangle($image, 2, 2, $width-3, $height-3, $border_color);

// Add text
$text1 = "GAMBAR TEST BERHASIL DIMUAT!";
$text2 = "Masalah .htaccess sudah diperbaiki";
$text3 = date('d F Y H:i:s');

imagestring($image, 5, 50, 60, $text1, $text_color);
imagestring($image, 3, 80, 90, $text2, $text_color);
imagestring($image, 2, 120, 120, $text3, $text_color);

// Save test image
$test_filename = 'access_test_' . date('YmdHis') . '.png';
$test_path = 'uploads/pengaduan/' . $test_filename;

if (imagepng($image, $test_path)) {
    echo "<p class='success'>✅ Test image berhasil dibuat: {$test_filename}</p>";
    
    // Test direct access
    echo "<h3>2. Test Direct Access ke Gambar:</h3>";
    echo "<p><strong>URL:</strong> <a href='{$test_path}' target='_blank'>{$test_path}</a></p>";
    
    echo "<h3>3. Preview Gambar:</h3>";
    echo "<img src='{$test_path}' alt='Test Image' onload=\"showSuccess(this)\" onerror=\"showError(this)\">";
    echo "<div id='result' style='margin-top: 10px;'></div>";
    
} else {
    echo "<p class='error'>❌ Gagal membuat test image</p>";
}

imagedestroy($image);

// List existing images
echo "<h3>4. Gambar Existing di Database:</h3>";
try {
    require_once 'config/database.php';
    
    $stmt = $pdo->query("SELECT id, nama_pengadu, judul, foto FROM pengaduan WHERE foto IS NOT NULL AND foto != '' ORDER BY id DESC LIMIT 3");
    $pengaduan_with_photos = $stmt->fetchAll();
    
    if (count($pengaduan_with_photos) > 0) {
        foreach ($pengaduan_with_photos as $p) {
            $image_path = 'uploads/pengaduan/' . $p['foto'];
            echo "<div class='info'>";
            echo "<h4>Pengaduan #{$p['id']} - {$p['nama_pengadu']}</h4>";
            echo "<p><strong>Judul:</strong> " . htmlspecialchars($p['judul']) . "</p>";
            echo "<p><strong>File:</strong> {$p['foto']}</p>";
            echo "<p><strong>URL:</strong> <a href='{$image_path}' target='_blank'>{$image_path}</a></p>";
            
            if (file_exists($image_path)) {
                echo "<p class='success'>✅ File exists</p>";
                echo "<img src='{$image_path}' alt='Dokumentasi Pengaduan' onload=\"console.log('Image loaded: {$image_path}')\" onerror=\"console.log('Image failed: {$image_path}')\">";
            } else {
                echo "<p class='error'>❌ File tidak ditemukan</p>";
            }
            echo "</div>";
        }
    } else {
        echo "<p>Belum ada pengaduan dengan foto.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error database: " . $e->getMessage() . "</p>";
}

echo "<h3>5. Perbaikan yang Telah Dilakukan:</h3>";
echo "<div class='info'>";
echo "<ul>";
echo "<li>✅ Memperbaiki .htaccess di folder uploads</li>";
echo "<li>✅ Menghapus rule yang memblokir semua file</li>";
echo "<li>✅ Tetap memblokir file berbahaya (PHP, scripts)</li>";
echo "<li>✅ Mengizinkan akses ke file gambar (JPG, PNG, GIF)</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><a href='?page=admin-pengaduan'>🔙 Test Admin Pengaduan</a> | <a href='debug_image.php'>🔍 Debug Detail</a></p>";
?>

<script>
function showSuccess(img) {
    document.getElementById('result').innerHTML = '<p class="success">✅ BERHASIL! Gambar dapat dimuat dengan baik.</p>';
}

function showError(img) {
    document.getElementById('result').innerHTML = '<p class="error">❌ GAGAL! Gambar masih tidak dapat dimuat.</p>';
}
</script>
