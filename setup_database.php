<?php
// Script untuk setup database otomatis
echo "<h2>Setup Database Kelurahan Margasari</h2>";

// Konfigurasi database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'margasari_kelurahan';

try {
    // Koneksi tanpa database terlebih dahulu
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Koneksi ke MySQL berhasil</p>";
    
    // Buat database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Database '$database' berhasil dibuat</p>";
    
    // Pilih database
    $pdo->exec("USE $database");
    
    // Baca dan eksekusi file SQL schema
    $sql_content = file_get_contents('config/database_schema.sql');
    
    // Split SQL berdasarkan delimiter ;
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
            try {
                $pdo->exec($statement);
                echo "<p>✅ Menjalankan: " . substr($statement, 0, 50) . "...</p>";
            } catch (Exception $e) {
                echo "<p>⚠️ Warning: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Update password default admin (untuk keamanan)
    $default_password = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$default_password]);
    
    echo "<h3>✅ Setup Database Selesai!</h3>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>Informasi Login Admin:</h4>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> password</p>";
    echo "<p><strong>URL Admin:</strong> <a href='?page=admin-login'>?page=admin-login</a></p>";
    echo "</div>";
    
    echo "<p><a href='index.php' style='background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali ke Website</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<h4>Langkah-langkah untuk mengatasi masalah:</h4>";
    echo "<ol>";
    echo "<li>Pastikan MySQL/XAMPP sudah running</li>";
    echo "<li>Periksa konfigurasi database di file config/database.php</li>";
    echo "<li>Pastikan user 'root' memiliki akses untuk membuat database</li>";
    echo "</ol>";
}
?>
