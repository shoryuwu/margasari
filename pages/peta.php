<?php
try {
    // Get data RT untuk markers
    $stmt = $pdo->query("
        SELECT rd.*, 
               COUNT(fu.id) as jumlah_fasilitas
        FROM rt_data rd
        LEFT JOIN fasilitas_umum fu ON rd.rt_number = fu.rt AND rd.rw_number = fu.rw
        GROUP BY rd.id
        ORDER BY rd.rw_number, rd.rt_number
    ");
    $rt_data = $stmt->fetchAll();
    
    // Get fasilitas umum
    $stmt = $pdo->query("SELECT * FROM fasilitas_umum WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    $fasilitas_data = $stmt->fetchAll();
    
} catch (Exception $e) {
    $rt_data = [];
    $fasilitas_data = [];
}
?>

<div class="container-fluid my-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <h2 class="fw-bold text-primary mb-3">
                    <i class="fas fa-map-marker-alt"></i> Peta Kelurahan Margasari
                </h2>
                <p class="text-muted">
                    Peta interaktif dengan informasi RT/RW dan fasilitas umum di Kelurahan Margasari
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Map Column -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-map"></i> Peta Interaktif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 500px; width: 100%;"></div>
                </div>
                <div class="card-footer">
                    <div class="row text-center">
                        <div class="col-4">
                            <strong class="text-primary"><?php echo count($rt_data); ?></strong><br>
                            <small class="text-muted">Total RT</small>
                        </div>
                        <div class="col-4">
                            <strong class="text-success"><?php echo count($fasilitas_data); ?></strong><br>
                            <small class="text-muted">Fasilitas Umum</small>
                        </div>
                        <div class="col-4">
                            <strong class="text-info">
                                <?php 
                                $total_penduduk = array_sum(array_column($rt_data, 'total_penduduk'));
                                echo number_format($total_penduduk);
                                ?>
                            </strong><br>
                            <small class="text-muted">Total Penduduk</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Legend -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle text-info"></i> Keterangan Peta
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary rounded-circle me-2" style="width: 15px; height: 15px;"></div>
                                <small>RT/RW Marker</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-success rounded-circle me-2" style="width: 15px; height: 15px;"></div>
                                <small>Fasilitas Pendidikan</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-danger rounded-circle me-2" style="width: 15px; height: 15px;"></div>
                                <small>Fasilitas Kesehatan</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-warning rounded-circle me-2" style="width: 15px; height: 15px;"></div>
                                <small>Fasilitas Ibadah</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-info rounded-circle me-2" style="width: 15px; height: 15px;"></div>
                                <small>Fasilitas Umum Lainnya</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Panel -->
        <div class="col-lg-4">
            <!-- RT/RW Data -->
            <div class="card shadow mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Data RT/RW
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>RT/RW</th>
                                    <th>KK</th>
                                    <th>Penduduk</th>
                                    <th>Ketua RT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rt_data as $rt): ?>
                                    <tr class="rt-row" 
                                        data-rt="<?php echo $rt['rt_number']; ?>" 
                                        data-rw="<?php echo $rt['rw_number']; ?>"
                                        style="cursor: pointer;">
                                        <td>
                                            <strong><?php echo $rt['rt_number']; ?>/<?php echo $rt['rw_number']; ?></strong>
                                        </td>
                                        <td><?php echo $rt['total_kk']; ?></td>
                                        <td><?php echo $rt['total_penduduk']; ?></td>
                                        <td>
                                            <small><?php echo htmlspecialchars($rt['ketua_rt']); ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Fasilitas Umum -->
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-building"></i> Fasilitas Umum
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $fasilitas_by_type = [];
                    foreach ($fasilitas_data as $fasilitas) {
                        $fasilitas_by_type[$fasilitas['jenis_fasilitas']][] = $fasilitas;
                    }
                    
                    $jenis_icons = [
                        'sekolah' => 'fa-school',
                        'puskesmas' => 'fa-hospital',
                        'masjid' => 'fa-mosque',
                        'taman' => 'fa-tree',
                        'pasar' => 'fa-store',
                        'lainnya' => 'fa-building'
                    ];
                    
                    foreach ($fasilitas_by_type as $jenis => $facilities):
                    ?>
                        <div class="mb-3">
                            <h6 class="text-<?php echo $jenis == 'sekolah' ? 'success' : ($jenis == 'puskesmas' ? 'danger' : ($jenis == 'masjid' ? 'warning' : 'info')); ?>">
                                <i class="fas <?php echo $jenis_icons[$jenis] ?? 'fa-building'; ?>"></i>
                                <?php echo ucfirst(str_replace('_', ' ', $jenis)); ?> (<?php echo count($facilities); ?>)
                            </h6>
                            <?php foreach ($facilities as $facility): ?>
                                <div class="facility-item ps-3 mb-2" 
                                     data-lat="<?php echo $facility['latitude']; ?>"
                                     data-lng="<?php echo $facility['longitude']; ?>"
                                     style="cursor: pointer;">
                                    <small class="d-block fw-bold"><?php echo htmlspecialchars($facility['nama_fasilitas']); ?></small>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($facility['alamat']); ?>
                                        <?php if ($facility['rt'] && $facility['rw']): ?>
                                            • RT <?php echo $facility['rt']; ?>/RW <?php echo $facility['rw']; ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize map
var map = L.map('map').setView([-1.2379, 116.8969], 14); // Coordinates for Balikpapan

// Add tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// RT Data untuk markers
var rtData = <?php echo json_encode($rt_data); ?>;
var fasilitasData = <?php echo json_encode($fasilitas_data); ?>;

// Colors for different facility types
var facilityColors = {
    'sekolah': 'green',
    'puskesmas': 'red', 
    'masjid': 'orange',
    'taman': 'lightgreen',
    'pasar': 'purple',
    'lainnya': 'blue'
};

// Add RT markers
rtData.forEach(function(rt, index) {
    // Use dummy coordinates if not available
    var lat = rt.latitude || (-1.2379 + (Math.random() - 0.5) * 0.02);
    var lng = rt.longitude || (116.8969 + (Math.random() - 0.5) * 0.02);
    
    var marker = L.marker([lat, lng], {
        icon: L.divIcon({
            html: '<div style="background-color: #0d6efd; color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">' + rt.rt_number + '</div>',
            className: 'rt-marker',
            iconSize: [25, 25]
        })
    }).addTo(map);
    
    // Popup content
    var popupContent = `
        <div style="min-width: 200px;">
            <h6><strong>RT ${rt.rt_number}/RW ${rt.rw_number}</strong></h6>
            <p class="mb-1"><i class="fas fa-user-tie"></i> <strong>Ketua RT:</strong> ${rt.ketua_rt}</p>
            ${rt.ketua_rt_phone ? `<p class="mb-1"><i class="fas fa-phone"></i> ${rt.ketua_rt_phone}</p>` : ''}
            <p class="mb-1"><i class="fas fa-home"></i> <strong>KK:</strong> ${rt.total_kk}</p>
            <p class="mb-1"><i class="fas fa-users"></i> <strong>Penduduk:</strong> ${rt.total_penduduk}</p>
            ${rt.wilayah_deskripsi ? `<p class="mb-0"><i class="fas fa-map-marker-alt"></i> ${rt.wilayah_deskripsi}</p>` : ''}
        </div>
    `;
    
    marker.bindPopup(popupContent);
});

// Add facility markers
fasilitasData.forEach(function(facility) {
    if (facility.latitude && facility.longitude) {
        var color = facilityColors[facility.jenis_fasilitas] || 'blue';
        
        var marker = L.marker([facility.latitude, facility.longitude], {
            icon: L.divIcon({
                html: '<div style="background-color: ' + color + '; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-building" style="font-size: 8px;"></i></div>',
                className: 'facility-marker',
                iconSize: [20, 20]
            })
        }).addTo(map);
        
        var popupContent = `
            <div style="min-width: 180px;">
                <h6><strong>${facility.nama_fasilitas}</strong></h6>
                <p class="mb-1"><i class="fas fa-tag"></i> ${facility.jenis_fasilitas.charAt(0).toUpperCase() + facility.jenis_fasilitas.slice(1)}</p>
                <p class="mb-1"><i class="fas fa-map-marker-alt"></i> ${facility.alamat}</p>
                ${facility.rt && facility.rw ? `<p class="mb-1"><i class="fas fa-home"></i> RT ${facility.rt}/RW ${facility.rw}</p>` : ''}
                <p class="mb-1"><i class="fas fa-info-circle"></i> <span class="badge bg-${facility.kondisi === 'baik' ? 'success' : (facility.kondisi === 'rusak_ringan' ? 'warning' : 'danger')}">${facility.kondisi.replace('_', ' ')}</span></p>
                ${facility.deskripsi ? `<p class="mb-0">${facility.deskripsi}</p>` : ''}
            </div>
        `;
        
        marker.bindPopup(popupContent);
    }
});

// Click events for RT rows
document.querySelectorAll('.rt-row').forEach(function(row) {
    row.addEventListener('click', function() {
        var rt = this.getAttribute('data-rt');
        var rw = this.getAttribute('data-rw');
        
        // Find corresponding marker and open popup
        rtData.forEach(function(rtItem, index) {
            if (rtItem.rt_number === rt && rtItem.rw_number === rw) {
                var lat = rtItem.latitude || (-1.2379 + (Math.random() - 0.5) * 0.02);
                var lng = rtItem.longitude || (116.8969 + (Math.random() - 0.5) * 0.02);
                
                map.setView([lat, lng], 16);
                
                // Highlight row
                document.querySelectorAll('.rt-row').forEach(r => r.classList.remove('table-active'));
                row.classList.add('table-active');
            }
        });
    });
});

// Click events for facility items
document.querySelectorAll('.facility-item').forEach(function(item) {
    item.addEventListener('click', function() {
        var lat = parseFloat(this.getAttribute('data-lat'));
        var lng = parseFloat(this.getAttribute('data-lng'));
        
        if (lat && lng) {
            map.setView([lat, lng], 17);
            
            // Highlight item
            document.querySelectorAll('.facility-item').forEach(f => f.classList.remove('bg-light'));
            this.classList.add('bg-light');
        }
    });
});
</script>
