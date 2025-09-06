CREATE DATABASE IF NOT EXISTS margasari_kelurahan;
USE margasari_kelurahan;

-- Tabel admin staff kelurahan
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'staff') DEFAULT 'staff',
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel RT (Rukun Tetangga)
CREATE TABLE IF NOT EXISTS rt_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rt_number VARCHAR(10) NOT NULL,
    rw_number VARCHAR(10) NOT NULL,
    ketua_rt VARCHAR(100) NOT NULL,
    ketua_rt_phone VARCHAR(20),
    total_kk INT DEFAULT 0,
    total_penduduk INT DEFAULT 0,
    wilayah_deskripsi TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel pengaduan
CREATE TABLE IF NOT EXISTS pengaduan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pengadu VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    rt VARCHAR(10),
    rw VARCHAR(10),
    alamat TEXT,
    jenis_pengaduan ENUM('infrastruktur', 'pelayanan', 'kebersihan', 'keamanan', 'lainnya') NOT NULL,
    judul VARCHAR(200) NOT NULL,
    isi_pengaduan TEXT NOT NULL,
    foto VARCHAR(255),
    status ENUM('pending', 'proses', 'selesai', 'ditolak') DEFAULT 'pending',
    tanggal_pengaduan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_proses TIMESTAMP NULL,
    tanggal_selesai TIMESTAMP NULL,
    catatan_admin TEXT,
    processed_by INT,
    FOREIGN KEY (processed_by) REFERENCES admin_users(id)
);

-- Tabel template surat
CREATE TABLE IF NOT EXISTS template_surat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_template VARCHAR(100) NOT NULL,
    jenis_surat ENUM('domisili', 'usaha', 'tidak_mampu', 'berkelakuan_baik', 'lainnya') NOT NULL,
    template_content TEXT NOT NULL,
    template_fields JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admin_users(id)
);

-- Tabel laporan kegiatan RT
CREATE TABLE IF NOT EXISTS laporan_kegiatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rt_number VARCHAR(10) NOT NULL,
    rw_number VARCHAR(10) NOT NULL,
    judul_kegiatan VARCHAR(200) NOT NULL,
    deskripsi_kegiatan TEXT NOT NULL,
    tanggal_kegiatan DATE NOT NULL,
    waktu_kegiatan TIME,
    tempat_kegiatan VARCHAR(200),
    jumlah_peserta INT,
    penanggung_jawab VARCHAR(100),
    foto_dokumentasi VARCHAR(255),
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admin_users(id)
);

-- Tabel fasilitas umum
CREATE TABLE IF NOT EXISTS fasilitas_umum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_fasilitas VARCHAR(100) NOT NULL,
    jenis_fasilitas ENUM('sekolah', 'puskesmas', 'masjid', 'taman', 'pasar', 'lainnya') NOT NULL,
    alamat TEXT NOT NULL,
    rt VARCHAR(10),
    rw VARCHAR(10),
    deskripsi TEXT,
    kondisi ENUM('baik', 'rusak_ringan', 'rusak_berat') DEFAULT 'baik',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel permintaan surat
CREATE TABLE IF NOT EXISTS permintaan_surat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pemohon VARCHAR(100) NOT NULL,
    nik VARCHAR(16) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    rt VARCHAR(10) NOT NULL,
    rw VARCHAR(10) NOT NULL,
    template_id INT NOT NULL,
    data_fields JSON NOT NULL,
    keperluan TEXT NOT NULL,
    status ENUM('pending', 'proses', 'selesai', 'ditolak') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    processed_by INT,
    catatan TEXT,
    FOREIGN KEY (template_id) REFERENCES template_surat(id),
    FOREIGN KEY (processed_by) REFERENCES admin_users(id)
);

-- Insert data admin default
INSERT INTO admin_users (username, password, name, role, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'super_admin', 'admin@margasari.kelurahan.go.id');

-- Insert data RT contoh
INSERT INTO rt_data (rt_number, rw_number, ketua_rt, ketua_rt_phone, total_kk, total_penduduk) VALUES 
('001', '001', 'Bapak Suharto', '081234567890', 45, 150),
('002', '001', 'Bapak Budi', '081234567891', 38, 125),
('003', '001', 'Bapak Ahmad', '081234567892', 42, 140),
('001', '002', 'Bapak Hasan', '081234567893', 35, 110),
('002', '002', 'Bapak Joko', '081234567894', 40, 135);

-- Insert template surat contoh
INSERT INTO template_surat (nama_template, jenis_surat, template_content, template_fields) VALUES 
('Surat Keterangan Domisili', 'domisili', 
'SURAT KETERANGAN DOMISILI

Yang bertanda tangan di bawah ini, Lurah Margasari Kecamatan Balikpapan Barat, menerangkan bahwa:

Nama         : [nama]
NIK          : [nik]
Tempat/Tgl Lahir : [tempat_lahir], [tanggal_lahir]
Jenis Kelamin    : [jenis_kelamin]
Alamat           : [alamat]
RT/RW            : [rt]/[rw]

Adalah benar bertempat tinggal di alamat tersebut di atas.

Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.', 
'["nama", "nik", "tempat_lahir", "tanggal_lahir", "jenis_kelamin", "alamat", "rt", "rw"]');
