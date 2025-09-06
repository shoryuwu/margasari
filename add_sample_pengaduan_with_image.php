<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>🧪 Menambahkan Pengaduan dengan Foto Dummy</h2>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { background: #e7f3ff; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
</style>";

try {
    // 1. Buat gambar placeholder sederhana
    echo "<h3>1. Membuat gambar placeholder...</h3>";
    
    $width = 800;
    $height = 600;
    $image = imagecreatetruecolor($width, $height);
    
    // Warna
    $bg_color = imagecolorallocate($image, 240, 248, 255); // Light blue
    $text_color = imagecolorallocate($image, 70, 130, 180); // Steel blue
    $border_color = imagecolorallocate($image, 30, 144, 255); // Dodger blue
    
    // Fill background
    imagefill($image, 0, 0, $bg_color);
    
    // Draw border
    imagerectangle($image, 5, 5, $width-6, $height-6, $border_color);
    imagerectangle($image, 10, 10, $width-11, $height-11, $border_color);
    
    // Add text
    $font_size = 5;
    $text1 = "FOTO DOKUMENTASI PENGADUAN";
    $text2 = "Contoh: Jalan Rusak di RT 001/RW 001";
    $text3 = "Kelurahan Margasari - Balikpapan Barat";
    $text4 = date('d F Y H:i:s');
    
    // Center text
    imagestring($image, $font_size, ($width - strlen($text1) * 10) / 2, 250, $text1, $text_color);
    imagestring($image, 4, ($width - strlen($text2) * 10) / 2, 290, $text2, $text_color);
    imagestring($image, 3, ($width - strlen($text3) * 8) / 2, 320, $text3, $text_color);
    imagestring($image, 2, ($width - strlen($text4) * 6) / 2, 350, $text4, $text_color);
    
    // Draw some shapes to simulate a road problem
    $pothole_color = imagecolorallocate($image, 139, 69, 19); // Saddle brown
    $road_color = imagecolorallocate($image, 105, 105, 105); // Dim gray
    
    // Draw road
    imagefilledrectangle($image, 100, 400, 700, 500, $road_color);
    
    // Draw potholes
    imagefilledellipse($image, 300, 450, 80, 40, $pothole_color);
    imagefilledellipse($image, 500, 460, 60, 30, $pothole_color);
    
    // Save image
    $filename = 'sample_' . date('YmdHis') . '.jpg';
    $filepath = 'uploads/pengaduan/' . $filename;
    
    if (imagejpeg($image, $filepath, 85)) {
        echo "<p class='success'>✅ Gambar berhasil dibuat: {$filename}</p>";
        imagedestroy($image);
    } else {
        echo "<p class='error'>❌ Gagal membuat gambar</p>";
        exit;
    }
    
    // 2. Insert pengaduan ke database
    echo "<h3>2. Menambahkan pengaduan ke database...</h3>";
    
    $pengaduan_data = [
        'nama_pengadu' => 'Ahmad Budiman',
        'email' => 'ahmad.budiman@email.com',
        'phone' => '081234567890',
        'rt' => '001',
        'rw' => '001',
        'alamat' => 'Jl. Margasari Raya No. 25, RT 001/RW 001',
        'jenis_pengaduan' => 'infrastruktur',
        'judul' => 'Jalan Rusak dan Berlubang di Depan Rumah',
        'isi_pengaduan' => 'Yang terhormat Bapak/Ibu Staff Kelurahan Margasari,

Dengan hormat, saya ingin melaporkan kondisi jalan di depan rumah yang sudah sangat rusak dan berlubang besar. Kondisi ini sangat membahayakan pengendara, terutama motor dan pejalan kaki.

Detail masalah:
- Lokasi: Jl. Margasari Raya No. 25 (depan rumah saya)
- Kondisi: Ada 2 lubang besar dengan diameter sekitar 80cm dan 60cm
- Kedalaman lubang sekitar 15-20cm
- Saat hujan, lubang terisi air dan tidak terlihat dari jauh
- Sudah ada beberapa motor yang jatuh karena lubang ini

Mohon kiranya dapat ditindaklanjuti untuk perbaikan jalan ini demi keselamatan warga.

Terima kasih atas perhatiannya.',
        'foto' => $filename,
        'status' => 'pending'
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO pengaduan (nama_pengadu, email, phone, rt, rw, alamat, jenis_pengaduan, judul, isi_pengaduan, foto, status, tanggal_pengaduan) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $pengaduan_data['nama_pengadu'],
        $pengaduan_data['email'],
        $pengaduan_data['phone'],
        $pengaduan_data['rt'],
        $pengaduan_data['rw'],
        $pengaduan_data['alamat'],
        $pengaduan_data['jenis_pengaduan'],
        $pengaduan_data['judul'],
        $pengaduan_data['isi_pengaduan'],
        $pengaduan_data['foto'],
        $pengaduan_data['status']
    ]);
    
    if ($result) {
        $pengaduan_id = $pdo->lastInsertId();
        echo "<p class='success'>✅ Pengaduan berhasil ditambahkan dengan ID: {$pengaduan_id}</p>";
        
        // 3. Show preview
        echo "<h3>3. Preview hasil...</h3>";
        echo "<div class='info'>";
        echo "<p><strong>Nama Pengadu:</strong> {$pengaduan_data['nama_pengadu']}</p>";
        echo "<p><strong>Judul:</strong> {$pengaduan_data['judul']}</p>";
        echo "<p><strong>Jenis:</strong> " . ucfirst($pengaduan_data['jenis_pengaduan']) . "</p>";
        echo "<p><strong>Status:</strong> " . ucfirst($pengaduan_data['status']) . "</p>";
        echo "<p><strong>Foto:</strong></p>";
        echo "<img src='{$filepath}' style='max-width: 300px; border: 1px solid #ccc; border-radius: 5px;' alt='Preview'>";
        echo "</div>";
        
        // 4. Tambahkan beberapa pengaduan lain (tanpa foto)
        echo "<h3>4. Menambahkan pengaduan lain...</h3>";
        
        $other_pengaduan = [
            [
                'nama_pengadu' => 'Siti Rahayu',
                'email' => 'siti.rahayu@email.com',
                'phone' => '081234567891',
                'rt' => '002',
                'rw' => '001',
                'alamat' => 'Jl. Balikpapan No. 15',
                'jenis_pengaduan' => 'kebersihan',
                'judul' => 'Sampah Menumpuk di TPS RT 002',
                'isi_pengaduan' => 'TPS di RT 002 sudah penuh dan tidak diangkut selama 3 hari. Menimbulkan bau tidak sedap.',
                'status' => 'proses'
            ],
            [
                'nama_pengadu' => 'Budi Santoso',
                'email' => 'budi.santoso@email.com',
                'phone' => '081234567892',
                'rt' => '003',
                'rw' => '001',
                'alamat' => 'Jl. Margasari Dalam No. 8',
                'jenis_pengaduan' => 'pelayanan',
                'judul' => 'Lampu Jalan Mati Total',
                'isi_pengaduan' => 'Lampu jalan di sepanjang Jl. Margasari Dalam sudah mati total sejak 1 minggu lalu.',
                'status' => 'selesai'
            ]
        ];
        
        foreach ($other_pengaduan as $data) {
            $stmt = $pdo->prepare("
                INSERT INTO pengaduan (nama_pengadu, email, phone, rt, rw, alamat, jenis_pengaduan, judul, isi_pengaduan, status, tanggal_pengaduan) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['nama_pengadu'],
                $data['email'],
                $data['phone'],
                $data['rt'],
                $data['rw'],
                $data['alamat'],
                $data['jenis_pengaduan'],
                $data['judul'],
                $data['isi_pengaduan'],
                $data['status']
            ]);
            
            echo "<p class='success'>✅ Ditambahkan: {$data['judul']}</p>";
        }
        
        echo "<div class='info'>";
        echo "<h4>🎉 Setup Berhasil!</h4>";
        echo "<p>Data pengaduan dengan foto telah berhasil ditambahkan.</p>";
        echo "<p><a href='?page=admin-login' class='btn' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Login Admin</a></p>";
        echo "<p><a href='?page=admin-pengaduan' class='btn' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-left:10px;'>Lihat Pengaduan</a></p>";
        echo "</div>";
        
    } else {
        echo "<p class='error'>❌ Gagal menambahkan pengaduan ke database</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Kembali ke Website</a></p>";
?>
