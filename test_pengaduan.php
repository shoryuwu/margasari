<?php
echo "<h2>🧪 Test Database Pengaduan</h2>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { background: #e7f3ff; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>";

try {
    // Load database config
    require_once 'config/database.php';
    echo "<div class='info'>✅ Database connected successfully</div>";
    
    // Test 1: Check if table exists
    echo "<h3>1. Checking if pengaduan table exists...</h3>";
    $tables = $pdo->query("SHOW TABLES LIKE 'pengaduan'")->fetchAll();
    if (count($tables) > 0) {
        echo "<p class='success'>✅ Table pengaduan exists</p>";
        
        // Show table structure
        echo "<h4>Table Structure:</h4>";
        $columns = $pdo->query("DESCRIBE pengaduan")->fetchAll();
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p class='error'>❌ Table pengaduan does not exist!</p>";
        echo "<p>Please run <a href='setup_database.php'>setup_database.php</a> first.</p>";
        exit;
    }
    
    // Test 2: Count existing records
    echo "<h3>2. Counting existing records...</h3>";
    $count = $pdo->query("SELECT COUNT(*) as total FROM pengaduan")->fetch();
    echo "<p>Current records: <strong>{$count['total']}</strong></p>";
    
    // Test 3: Insert sample data
    echo "<h3>3. Inserting test data...</h3>";
    
    $test_data = [
        'nama_pengadu' => 'Test User - ' . date('Y-m-d H:i:s'),
        'email' => 'test@example.com',
        'phone' => '081234567890',
        'rt' => '001',
        'rw' => '001', 
        'alamat' => 'Alamat test',
        'jenis_pengaduan' => 'infrastruktur',
        'judul' => 'Test Pengaduan - ' . date('H:i:s'),
        'isi_pengaduan' => 'Ini adalah test pengaduan untuk memastikan database berfungsi dengan baik.',
        'foto' => null
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO pengaduan (nama_pengadu, email, phone, rt, rw, alamat, jenis_pengaduan, judul, isi_pengaduan, foto) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $test_data['nama_pengadu'],
        $test_data['email'],
        $test_data['phone'],
        $test_data['rt'],
        $test_data['rw'],
        $test_data['alamat'],
        $test_data['jenis_pengaduan'],
        $test_data['judul'],
        $test_data['isi_pengaduan'],
        $test_data['foto']
    ]);
    
    if ($result) {
        $insert_id = $pdo->lastInsertId();
        echo "<p class='success'>✅ Test data inserted successfully! ID: {$insert_id}</p>";
    } else {
        echo "<p class='error'>❌ Failed to insert test data</p>";
    }
    
    // Test 4: Show recent records
    echo "<h3>4. Recent pengaduan records...</h3>";
    $recent = $pdo->query("
        SELECT id, nama_pengadu, jenis_pengaduan, judul, status, tanggal_pengaduan 
        FROM pengaduan 
        ORDER BY tanggal_pengaduan DESC 
        LIMIT 10
    ")->fetchAll();
    
    if (count($recent) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nama</th><th>Jenis</th><th>Judul</th><th>Status</th><th>Tanggal</th></tr>";
        foreach ($recent as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nama_pengadu']}</td>";
            echo "<td>{$row['jenis_pengaduan']}</td>";
            echo "<td>" . substr($row['judul'], 0, 50) . "</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>{$row['tanggal_pengaduan']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No records found.</p>";
    }
    
    // Test 5: Test form functions
    echo "<h3>5. Testing helper functions...</h3>";
    
    require_once 'includes/functions.php';
    
    // Test sanitize_input
    $test_input = "<script>alert('test')</script>Hello";
    $sanitized = sanitize_input($test_input);
    echo "<p>Sanitize test: '{$test_input}' → '{$sanitized}'</p>";
    
    // Test CSRF token
    session_start();
    $token = generate_csrf_token();
    $verify = verify_csrf_token($token);
    echo "<p>CSRF token test: " . ($verify ? "✅ Working" : "❌ Failed") . "</p>";
    
    echo "<div class='info'>🎉 All tests completed!</div>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Error details:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>📋 Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='?page=pengaduan'>Test Pengaduan Form</a></li>";
echo "<li><a href='?page=admin-login'>Login to Admin Panel</a></li>";
echo "<li><a href='setup_database.php'>Run Database Setup</a></li>";
echo "<li><a href='index.php'>Back to Website</a></li>";
echo "</ul>";
?>
