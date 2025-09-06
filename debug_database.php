<?php
session_start();
require_once 'config/database.php';

echo "<h2>🔍 Debug Database Kelurahan Margasari</h2>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { background: #e7f3ff; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
.warning { background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 10px 0; }
.debug-section { margin: 30px 0; }
</style>";

try {
    echo "<div class='info'>✅ Koneksi database berhasil!</div>";
    
    // 1. Cek apakah tabel ada
    echo "<div class='debug-section'>";
    echo "<h3>📋 Daftar Tabel di Database</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li><strong>$table</strong></li>";
    }
    echo "</ul>";
    echo "</div>";
    
    // 2. Cek struktur tabel pengaduan
    if (in_array('pengaduan', $tables)) {
        echo "<div class='debug-section'>";
        echo "<h3>🗂️ Struktur Tabel Pengaduan</h3>";
        $columns = $pdo->query("DESCRIBE pengaduan")->fetchAll();
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        
        // 3. Cek data pengaduan
        echo "<div class='debug-section'>";
        echo "<h3>📊 Data Pengaduan</h3>";
        $pengaduan = $pdo->query("SELECT COUNT(*) as total FROM pengaduan")->fetch();
        echo "<p><strong>Total pengaduan:</strong> " . $pengaduan['total'] . "</p>";
        
        if ($pengaduan['total'] > 0) {
            $pengaduan_data = $pdo->query("SELECT * FROM pengaduan ORDER BY tanggal_pengaduan DESC LIMIT 10")->fetchAll();
            echo "<table>";
            echo "<tr><th>ID</th><th>Nama</th><th>Jenis</th><th>Judul</th><th>Status</th><th>Tanggal</th></tr>";
            foreach ($pengaduan_data as $p) {
                echo "<tr>";
                echo "<td>{$p['id']}</td>";
                echo "<td>{$p['nama_pengadu']}</td>";
                echo "<td>{$p['jenis_pengaduan']}</td>";
                echo "<td>" . substr($p['judul'], 0, 50) . "...</td>";
                echo "<td>{$p['status']}</td>";
                echo "<td>{$p['tanggal_pengaduan']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='warning'>⚠️ Tidak ada data pengaduan di database!</div>";
            
            // Buat data contoh
            echo "<h4>💡 Menambahkan Data Pengaduan Contoh</h4>";
            
            $sample_data = [
                [
                    'nama_pengadu' => 'Budi Santoso',
                    'email' => 'budi@email.com',
                    'phone' => '081234567890',
                    'rt' => '001',
                    'rw' => '001',
                    'alamat' => 'Jl. Margasari No. 15',
                    'jenis_pengaduan' => 'infrastruktur',
                    'judul' => 'Jalan Rusak di Depan Rumah',
                    'isi_pengaduan' => 'Mohon perbaikan jalan yang rusak di depan rumah. Sudah berlubang besar dan berbahaya untuk kendaraan.',
                    'status' => 'pending'
                ],
                [
                    'nama_pengadu' => 'Siti Aminah',
                    'email' => 'siti@email.com', 
                    'phone' => '081234567891',
                    'rt' => '002',
                    'rw' => '001',
                    'alamat' => 'Jl. Balikpapan Raya No. 25',
                    'jenis_pengaduan' => 'kebersihan',
                    'judul' => 'Sampah Menumpuk di TPS',
                    'isi_pengaduan' => 'TPS dekat rumah sudah penuh dan tidak diangkut. Menimbulkan bau tidak sedap.',
                    'status' => 'proses'
                ],
                [
                    'nama_pengadu' => 'Ahmad Rahman',
                    'email' => 'ahmad@email.com',
                    'phone' => '081234567892', 
                    'rt' => '001',
                    'rw' => '002',
                    'alamat' => 'Jl. Margasari Dalam No. 8',
                    'jenis_pengaduan' => 'pelayanan',
                    'judul' => 'Pelayanan Administrasi Lambat',
                    'isi_pengaduan' => 'Proses pembuatan surat keterangan domisili memakan waktu terlalu lama.',
                    'status' => 'selesai'
                ]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO pengaduan (nama_pengadu, email, phone, rt, rw, alamat, jenis_pengaduan, judul, isi_pengaduan, status, tanggal_pengaduan) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            foreach ($sample_data as $data) {
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
            
            echo "<div class='info'>🎉 Data pengaduan contoh berhasil ditambahkan!</div>";
        }
        echo "</div>";
        
    } else {
        echo "<div class='error'>❌ Tabel pengaduan tidak ditemukan! Jalankan setup_database.php</div>";
    }
    
    // 4. Cek tabel lainnya
    $other_tables = ['admin_users', 'rt_data', 'template_surat', 'laporan_kegiatan', 'fasilitas_umum'];
    echo "<div class='debug-section'>";
    echo "<h3>📈 Status Tabel Lainnya</h3>";
    foreach ($other_tables as $table_name) {
        if (in_array($table_name, $tables)) {
            $count = $pdo->query("SELECT COUNT(*) as total FROM $table_name")->fetch();
            echo "<p>✅ <strong>$table_name:</strong> {$count['total']} records</p>";
        } else {
            echo "<p>❌ <strong>$table_name:</strong> Tabel tidak ada</p>";
        }
    }
    echo "</div>";
    
    // 5. Test form pengaduan
    echo "<div class='debug-section'>";
    echo "<h3>🧪 Test Form Pengaduan</h3>";
    echo "<p>Silakan test form pengaduan: <a href='?page=pengaduan' target='_blank'>Form Pengaduan</a></p>";
    echo "<p>Atau lihat admin panel: <a href='?page=admin-login' target='_blank'>Admin Login</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error database: " . $e->getMessage() . "</div>";
    
    echo "<div class='debug-section'>";
    echo "<h3>🔧 Langkah Perbaikan</h3>";
    echo "<ol>";
    echo "<li>Pastikan XAMPP MySQL sudah running</li>";
    echo "<li>Jalankan <a href='setup_database.php'>setup_database.php</a></li>";
    echo "<li>Periksa konfigurasi database di config/database.php</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<br><hr>";
echo "<p><a href='index.php'>← Kembali ke Website</a> | <a href='setup_database.php'>🔧 Setup Database</a></p>";
?>
