# Website Kelurahan Margasari - Balikpapan Barat

Website resmi Kelurahan Margasari dengan sistem informasi terintegrasi untuk pelayanan masyarakat, sistem arsip RT, dan manajemen data kelurahan.

## 🌟 Fitur Utama

### A. Sistem Pengaduan Online
- Form pengaduan masyarakat yang mudah digunakan
- Upload foto dokumentasi untuk memperkuat pengaduan
- Sistem status tracking (pending, proses, selesai, ditolak)
- Dashboard admin untuk mengelola pengaduan
- Notifikasi real-time kepada masyarakat

### B. Sistem Template Surat
- Template surat keterangan (domisili, usaha, tidak mampu, berkelakuan baik, dll)
- Form permohonan surat online
- Sistem persetujuan dan tracking status
- Generate surat otomatis dengan data yang diinput
- Arsip digital semua surat yang dikeluarkan

### C. Sistem Pelaporan Kegiatan RT
- Form input kegiatan RT dengan upload foto dokumentasi
- Target minimal 5 kegiatan per bulan per RT
- Gallery dokumentasi kegiatan
- Filter berdasarkan RT/RW dan tanggal
- Dashboard statistik kegiatan per RT

### D. Peta Kelurahan Interaktif
- Peta interaktif dengan Leaflet Maps
- Marker untuk setiap RT/RW dengan data kependudukan
- Informasi fasilitas umum (sekolah, puskesmas, masjid, dll)
- Data statistik per RT (jumlah KK, penduduk, fasilitas)
- Informasi kontak Ketua RT

### E. Dashboard Admin Staff Kelurahan
- Login system untuk staff kelurahan
- Dashboard dengan statistik real-time
- Manajemen pengaduan, surat, dan kegiatan RT
- Sistem user management (untuk super admin)
- Laporan dan export data

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 8.0+ dengan PDO untuk database
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework CSS**: Bootstrap 5.3
- **Icons**: Font Awesome 6.0
- **Maps**: Leaflet.js untuk peta interaktif
- **Database**: MySQL 8.0+
- **Server**: Apache/Nginx (XAMPP untuk development)

## 📋 Persyaratan Sistem

- PHP 8.0 atau lebih baru
- MySQL 8.0 atau MariaDB 10.4+
- Apache/Nginx web server
- Ekstensi PHP yang diperlukan:
  - PDO dan PDO_MySQL
  - GD (untuk manipulasi gambar)
  - mbstring
  - fileinfo
  - openssl

## ⚙️ Instalasi dan Setup

### 1. Download dan Extract
```bash
git clone atau download project ini
extract ke folder web server (htdocs untuk XAMPP)
```

### 2. Setup Database
1. Buka browser dan akses `http://localhost/margasari/setup_database.php`
2. Script akan otomatis membuat database dan tabel yang diperlukan
3. Data admin default akan dibuat:
   - **Username**: admin
   - **Password**: password

### 3. Konfigurasi Database (Opsional)
Edit file `config/database.php` jika perlu mengubah:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', '');
define('DB_NAME', 'margasari_kelurahan');
```

### 4. Setup Permissions
Pastikan folder berikut memiliki permission write:
```bash
chmod 755 uploads/
chmod 755 uploads/activities/
chmod 755 uploads/documents/
chmod 755 uploads/pengaduan/
```

### 5. Akses Website
- **Website Publik**: `http://localhost/margasari/`
- **Admin Panel**: `http://localhost/margasari/?page=admin-login`

## 👤 Login Admin Default

- **Username**: `admin`
- **Password**: `password`
- **Role**: Super Admin

⚠️ **PENTING**: Segera ganti password default setelah login pertama!

## 📁 Struktur File

```
margasari/
├── config/
│   ├── database.php           # Konfigurasi database
│   └── database_schema.sql    # Schema database
├── includes/
│   ├── functions.php          # Helper functions
│   ├── header.php            # Header website
│   ├── navbar.php            # Navigation bar
│   └── footer.php            # Footer website
├── pages/
│   ├── home.php              # Halaman utama
│   ├── pengaduan.php         # Form pengaduan
│   ├── kegiatan.php          # List kegiatan RT
│   ├── peta.php              # Peta interaktif
│   ├── template_surat.php    # Template surat
│   ├── admin_login.php       # Login admin
│   ├── admin_dashboard.php   # Dashboard admin
│   └── ...                   # Halaman lainnya
├── assets/
│   ├── css/style.css         # Custom CSS
│   ├── js/main.js           # JavaScript utama
│   └── images/              # Logo dan gambar
├── uploads/                  # Folder upload file
│   ├── activities/          # Foto kegiatan RT
│   ├── documents/           # Dokumen surat
│   └── pengaduan/           # Foto pengaduan
├── index.php                # Entry point utama
├── setup_database.php       # Script setup database
└── README.md               # Dokumentasi ini
```

## 🗄️ Struktur Database

### Tabel Utama:
1. **admin_users** - Data admin dan staff kelurahan
2. **rt_data** - Data RT/RW dan kependudukan
3. **pengaduan** - Data pengaduan masyarakat
4. **template_surat** - Template surat keterangan
5. **permintaan_surat** - Permohonan surat dari masyarakat
6. **laporan_kegiatan** - Laporan kegiatan RT
7. **fasilitas_umum** - Data fasilitas umum kelurahan

## 🔧 Kustomisasi

### Mengubah Informasi Kelurahan
Edit file berikut untuk menyesuaikan dengan kelurahan Anda:
- `includes/header.php` - Nama kelurahan dan kontak
- `includes/footer.php` - Alamat dan informasi kontak
- `includes/navbar.php` - Menu navigasi

### Menambah Template Surat
1. Login sebagai admin
2. Masuk ke menu "Template Surat"
3. Tambah template baru dengan field yang diperlukan
4. Template akan otomatis tersedia di form permohonan

### Kustomisasi Peta
Edit koordinat di file `pages/peta.php`:
```javascript
var map = L.map('map').setView([-1.2379, 116.8969], 14);
// Ganti dengan koordinat kelurahan Anda
```

## 🔒 Keamanan

### Fitur Keamanan yang Diimplementasikan:
- CSRF Token protection pada semua form
- Input sanitization dan validation
- Password hashing dengan bcrypt
- Session management yang aman
- File upload restrictions
- SQL injection protection dengan prepared statements

### Rekomendasi Keamanan Tambahan:
1. Ganti password admin default
2. Gunakan HTTPS di production
3. Backup database secara berkala
4. Update PHP dan dependencies secara rutin
5. Implementasi rate limiting untuk form submission

## 📊 Fitur Admin Panel

### Dashboard Admin meliputi:
- Statistik pengaduan, kegiatan, dan permintaan surat
- Grafik aktivitas real-time
- Notifikasi pengaduan pending
- Quick actions untuk approval

### Manajemen Data:
- **Pengaduan**: Review, approve, atau tolak pengaduan
- **Surat**: Process dan generate surat keterangan
- **Kegiatan RT**: Moderate dan publish kegiatan RT
- **Data RT**: Manage data RT/RW dan kependudukan
- **Fasilitas**: Kelola data fasilitas umum
- **Users**: Manage staff kelurahan (super admin only)

## 🚀 Deployment ke Production

### 1. Persiapan Server
```bash
# Update system
sudo apt update && sudo apt upgrade

# Install LAMP stack
sudo apt install apache2 mysql-server php php-mysql php-gd php-mbstring
```

### 2. Database Production
```sql
CREATE DATABASE margasari_kelurahan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'margasari_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON margasari_kelurahan.* TO 'margasari_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Konfigurasi Apache
```apache
<VirtualHost *:80>
    ServerName margasari.kelurahan.go.id
    DocumentRoot /var/www/margasari
    ErrorLog ${APACHE_LOG_DIR}/margasari_error.log
    CustomLog ${APACHE_LOG_DIR}/margasari_access.log combined
</VirtualHost>
```

### 4. SSL Certificate (Recommended)
```bash
# Install Let's Encrypt
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d margasari.kelurahan.go.id
```

## 📞 Support dan Bantuan

### Jika mengalami masalah:
1. Cek error log di browser console
2. Periksa file log Apache/Nginx
3. Pastikan semua dependencies terinstall
4. Verify database connection dan permissions

### Common Issues:
- **500 Error**: Cek permission folder dan file
- **Database Error**: Verify credentials di config/database.php
- **Upload Error**: Cek permission folder uploads/
- **Map tidak muncul**: Pastikan koneksi internet untuk load tiles

## 📝 Changelog

### v1.0.0 (2024)
- Initial release
- Sistem pengaduan online
- Template surat keterangan
- Pelaporan kegiatan RT
- Peta kelurahan interaktif
- Dashboard admin lengkap

## 📄 Lisensi

Project ini dibuat untuk keperluan pelayanan publik Kelurahan Margasari.

## 🤝 Kontribusi

Untuk improvement atau bug reports, silakan hubungi tim pengembang atau staff kelurahan.

---

**Website Kelurahan Margasari** - Melayani dengan Transparansi dan Responsif