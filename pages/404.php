<div class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <div class="error-number text-primary" style="font-size: 8rem; font-weight: 900;">
                    404
                </div>
                <h2 class="mb-3">Halaman Tidak Ditemukan</h2>
                <p class="text-muted mb-4">
                    Maaf, halaman yang Anda cari tidak dapat ditemukan. 
                    Mungkin halaman telah dipindahkan atau URL yang dimasukkan salah.
                </p>
                
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Kembali ke Beranda
                    </a>
                    <a href="?page=pengaduan" class="btn btn-outline-primary">
                        <i class="fas fa-comment-dots"></i> Pengaduan
                    </a>
                    <a href="?page=kegiatan" class="btn btn-outline-primary">
                        <i class="fas fa-calendar-alt"></i> Kegiatan RT
                    </a>
                </div>
                
                <hr class="my-5">
                
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-phone text-primary"></i> Hubungi Kami</h5>
                        <p class="text-muted">
                            Telepon: (0542) 123-456<br>
                            Email: info@margasari.go.id
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-map-marker-alt text-primary"></i> Alamat</h5>
                        <p class="text-muted">
                            Jl. Margasari No. 123<br>
                            Balikpapan Barat, Kota Balikpapan
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-page {
    animation: fadeIn 0.8s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
