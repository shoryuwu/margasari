<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-home"></i> Margasari
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($page == 'home') ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-home"></i> Beranda
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profilDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-info-circle"></i> Profil
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?page=profil-kelurahan">Profil Kelurahan</a></li>
                        <li><a class="dropdown-item" href="?page=struktur-organisasi">Struktur Organisasi</a></li>
                        <li><a class="dropdown-item" href="?page=data-rt">Data RT/RW</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="layananDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs"></i> Layanan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?page=pengaduan">
                            <i class="fas fa-comment-dots"></i> Pengaduan Online
                        </a></li>
                        <li><a class="dropdown-item" href="?page=permintaan-surat">
                            <i class="fas fa-file-alt"></i> Permintaan Surat
                        </a></li>
                        <li><a class="dropdown-item" href="?page=template-surat">
                            <i class="fas fa-file-contract"></i> Template Surat
                        </a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($page == 'kegiatan') ? 'active' : ''; ?>" href="?page=kegiatan">
                        <i class="fas fa-calendar-alt"></i> Kegiatan RT
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($page == 'peta') ? 'active' : ''; ?>" href="?page=peta">
                        <i class="fas fa-map-marker-alt"></i> Peta Kelurahan
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="?page=admin-login">
                        <i class="fas fa-user-shield"></i> Admin Login
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
