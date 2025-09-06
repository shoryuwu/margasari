<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>📅 Menambahkan Sample Kegiatan RT</h2>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 15px 0; border-radius: 5px; }
.step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 8px; border: 1px solid #dee2e6; }
img { max-width: 200px; height: auto; border: 2px solid #28a745; margin: 5px; border-radius: 5px; }
</style>";

echo "<div class='container'>";

try {
    echo "<h3>🎨 Membuat Foto Dokumentasi Sample</h3>";
    
    $sample_images = [];
    
    if (extension_loaded('gd')) {
        echo "<p class='success'>✅ PHP GD Extension tersedia</p>";
        
        // Buat 3 gambar sample untuk kegiatan yang berbeda
        $activities = [
            [
                'title' => 'KERJA BAKTI LINGKUNGAN',
                'subtitle' => 'RT 001/RW 001 - Margasari',
                'desc' => 'Membersihkan selokan dan area umum',
                'color' => [40, 167, 69] // Green
            ],
            [
                'title' => 'RAPAT BULANAN RT',
                'subtitle' => 'RT 002/RW 001 - Margasari', 
                'desc' => 'Pembahasan program kerja bulan depan',
                'color' => [52, 144, 220] // Blue
            ],
            [
                'title' => 'POSYANDU BALITA',
                'subtitle' => 'RT 003/RW 001 - Margasari',
                'desc' => 'Pemeriksaan kesehatan rutin balita',
                'color' => [220, 53, 69] // Red
            ]
        ];
        
        foreach ($activities as $index => $activity) {
            $width = 600;
            $height = 400;
            $image = imagecreatetruecolor($width, $height);
            
            // Colors
            $white = imagecolorallocate($image, 255, 255, 255);
            $primary_color = imagecolorallocate($image, $activity['color'][0], $activity['color'][1], $activity['color'][2]);
            $dark = imagecolorallocate($image, 33, 37, 41);
            $light_gray = imagecolorallocate($image, 248, 249, 250);
            
            // Background
            imagefill($image, 0, 0, $white);
            
            // Header
            imagefilledrectangle($image, 0, 0, $width, 100, $primary_color);
            
            // Content area
            imagefilledrectangle($image, 20, 120, $width-20, $height-20, $light_gray);
            
            // Text
            imagestring($image, 5, 120, 35, $activity['title'], $white);
            imagestring($image, 4, 160, 65, $activity['subtitle'], $white);
            imagestring($image, 3, 40, 150, $activity['desc'], $dark);
            imagestring($image, 3, 40, 180, 'Tanggal: ' . date('d F Y'), $dark);
            imagestring($image, 2, 40, 210, 'Dokumentasi Kegiatan RT - Kelurahan Margasari', $dark);
            
            // Add some visual elements
            imagefilledrectangle($image, 40, 250, 560, 350, $primary_color);
            imagestring($image, 4, 200, 285, 'KEGIATAN RT SUKSES!', $white);
            imagestring($image, 3, 160, 315, 'Partisipasi warga sangat antusias', $white);
            
            // Save
            $filename = 'kegiatan_' . ($index + 1) . '_' . date('YmdHis') . '.jpg';
            $filepath = 'uploads/activities/' . $filename;
            
            if (imagejpeg($image, $filepath, 85)) {
                echo "<p class='success'>✅ Gambar " . ($index + 1) . " berhasil dibuat: {$filename}</p>";
                echo "<img src='{$filepath}' alt='Sample {$activity['title']}'>";
                $sample_images[] = $filename;
                imagedestroy($image);
            } else {
                echo "<p class='error'>❌ Gagal membuat gambar " . ($index + 1) . "</p>";
            }
        }
    } else {
        echo "<p class='error'>❌ PHP GD Extension tidak tersedia</p>";
    }
    
    echo "<hr>";
    echo "<h3>💾 Menambahkan Data Kegiatan ke Database</h3>";
    
    // Sample kegiatan data
    $kegiatan_data = [
        [
            'rt_number' => '001',
            'rw_number' => '001',
            'judul_kegiatan' => 'Kerja Bakti Lingkungan RT 001',
            'deskripsi_kegiatan' => 'Kegiatan kerja bakti bulanan yang meliputi pembersihan selokan, perbaikan jalan kecil, dan penataan taman RT. Kegiatan ini diikuti oleh seluruh warga RT 001 dengan antusias tinggi. Hasil kerja bakti berupa lingkungan yang lebih bersih dan tertata rapi.',
            'tanggal_kegiatan' => date('Y-m-d', strtotime('-5 days')),
            'waktu_kegiatan' => '07:00:00',
            'tempat_kegiatan' => 'Area RT 001 - Jl. Margasari',
            'jumlah_peserta' => 32,
            'penanggung_jawab' => 'Pak Suharto (Ketua RT)',
            'foto_dokumentasi' => isset($sample_images[0]) ? $sample_images[0] : null,
            'status' => 'published'
        ],
        [
            'rt_number' => '002',
            'rw_number' => '001',
            'judul_kegiatan' => 'Rapat Bulanan RT 002',
            'deskripsi_kegiatan' => 'Rapat koordinasi bulanan RT 002 membahas program kerja bulan depan, evaluasi kegiatan bulan lalu, dan pembahasan iuran warga. Rapat dihadiri oleh pengurus RT dan perwakilan dari setiap blok rumah.',
            'tanggal_kegiatan' => date('Y-m-d', strtotime('-3 days')),
            'waktu_kegiatan' => '19:30:00',
            'tempat_kegiatan' => 'Balai RT 002',
            'jumlah_peserta' => 15,
            'penanggung_jawab' => 'Pak Budi (Ketua RT)',
            'foto_dokumentasi' => isset($sample_images[1]) ? $sample_images[1] : null,
            'status' => 'published'
        ],
        [
            'rt_number' => '003',
            'rw_number' => '001',
            'judul_kegiatan' => 'Posyandu Balita RT 003',
            'deskripsi_kegiatan' => 'Kegiatan posyandu rutin bulanan untuk pemeriksaan kesehatan balita, imunisasi, dan penyuluhan gizi. Kegiatan ini bekerjasama dengan puskesmas setempat dan dibantu oleh kader kesehatan RT.',
            'tanggal_kegiatan' => date('Y-m-d', strtotime('-1 days')),
            'waktu_kegiatan' => '08:00:00',
            'tempat_kegiatan' => 'Pos RT 003',
            'jumlah_peserta' => 25,
            'penanggung_jawab' => 'Bu Siti (Kader Kesehatan)',
            'foto_dokumentasi' => isset($sample_images[2]) ? $sample_images[2] : null,
            'status' => 'published'
        ],
        [
            'rt_number' => '001',
            'rw_number' => '002',
            'judul_kegiatan' => 'Pelatihan Kewirausahaan Warga',
            'deskripsi_kegiatan' => 'Pelatihan kewirausahaan untuk ibu-ibu RT 001/RW 002 dengan materi pembuatan kue kering dan strategi pemasaran online. Pelatihan diharapkan dapat meningkatkan ekonomi keluarga.',
            'tanggal_kegiatan' => date('Y-m-d'),
            'waktu_kegiatan' => '09:00:00',
            'tempat_kegiatan' => 'Rumah Pak Hasan',
            'jumlah_peserta' => 20,
            'penanggung_jawab' => 'Bu Aminah',
            'foto_dokumentasi' => null,
            'status' => 'draft'
        ],
        [
            'rt_number' => '002',
            'rw_number' => '002',
            'judul_kegiatan' => 'Lomba 17 Agustus RT 002',
            'deskripsi_kegiatan' => 'Persiapan lomba memperingati HUT RI ke-78 dengan berbagai kategori: balap karung, makan kerupuk, dan tarik tambang. Kegiatan akan melibatkan seluruh warga dari anak-anak hingga orang tua.',
            'tanggal_kegiatan' => date('Y-m-d', strtotime('+3 days')),
            'waktu_kegiatan' => '15:00:00',
            'tempat_kegiatan' => 'Lapangan RT 002',
            'jumlah_peserta' => 50,
            'penanggung_jawab' => 'Pak Joko (Ketua RT)',
            'foto_dokumentasi' => null,
            'status' => 'draft'
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO laporan_kegiatan (rt_number, rw_number, judul_kegiatan, deskripsi_kegiatan, tanggal_kegiatan, waktu_kegiatan, tempat_kegiatan, jumlah_peserta, penanggung_jawab, foto_dokumentasi, status, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    
    $inserted_count = 0;
    foreach ($kegiatan_data as $data) {
        try {
            $result = $stmt->execute([
                $data['rt_number'],
                $data['rw_number'],
                $data['judul_kegiatan'],
                $data['deskripsi_kegiatan'],
                $data['tanggal_kegiatan'],
                $data['waktu_kegiatan'],
                $data['tempat_kegiatan'],
                $data['jumlah_peserta'],
                $data['penanggung_jawab'],
                $data['foto_dokumentasi'],
                $data['status']
            ]);
            
            if ($result) {
                $inserted_count++;
                echo "<p class='success'>✅ Ditambahkan: {$data['judul_kegiatan']}</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: {$data['judul_kegiatan']} - " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<div class='info'>";
    echo "<h4>🎉 Setup Kegiatan RT Berhasil!</h4>";
    echo "<p><strong>{$inserted_count}</strong> kegiatan berhasil ditambahkan ke database.</p>";
    echo "<p><strong>" . count($sample_images) . "</strong> foto dokumentasi berhasil dibuat.</p>";
    
    echo "<h5>📊 Statistik:</h5>";
    echo "<ul>";
    echo "<li>✅ <strong>3 kegiatan published</strong> (tampil di website)</li>";
    echo "<li>✅ <strong>2 kegiatan draft</strong> (belum dipublikasi)</li>";
    echo "<li>✅ <strong>3 kegiatan dengan foto</strong> dokumentasi</li>";
    echo "<li>✅ <strong>Data dari berbagai RT</strong> (001, 002, 003)</li>";
    echo "</ul>";
    
    echo "<h5>🔗 Next Steps:</h5>";
    echo "<p><a href='?page=admin-login' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Login Admin</a></p>";
    echo "<p><a href='?page=admin-kegiatan' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-left:10px;'>Kelola Kegiatan RT</a></p>";
    echo "<p><a href='?page=kegiatan' style='background:#17a2b8;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-left:10px;'>Lihat di Website</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<hr>";
echo "<p><a href='index.php'>← Kembali ke Website</a></p>";
?>
