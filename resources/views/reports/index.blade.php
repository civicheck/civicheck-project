@extends('layouts.app')

@section('title', 'Dashboard CIVICHECK')

@section('content')
<div class="hero p-4 p-md-5 mb-4">
    <h1 class="fw-bold">Pantau Infrastruktur di Sekitarmu</h1>
    <p class="mb-3">Laporkan kerusakan jalan dan drainase agar dapat ditindaklanjuti.</p>
    <a class="btn btn-warning fw-semibold" href="{{ route('reports.create') }}">Buat Laporan</a>
</div>

<!-- KOTAK RINGKASAN -->
<div class="row g-3 mb-4">
    @foreach([
        ['Total Laporan', $reports->count()],
        ['Menunggu', $reports->where('status','Menunggu')->count()],
        ['Diproses', $reports->where('status','Diproses')->count()],
        ['Selesai', $reports->where('status','Selesai')->count()]
    ] as $item)
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm p-3">
            <div class="text-secondary small">{{ $item[0] }}</div>
            <div class="fs-2 fw-bold">{{ $item[1] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row mb-4">
    <!-- BAGIAN GRAFIK BARU -->
    <div class="col-md-5 mb-3">
        <div class="card shadow-sm p-3 h-100">
            <h5 class="fw-bold">Statistik Kerusakan</h5>
            <p class="text-secondary small">Berdasarkan jenis infrastruktur.</p>
            <div class="d-flex justify-content-center align-items-center" style="height: 250px;">
                <canvas id="chartJenis"></canvas>
            </div>
        </div>
    </div>

    <!-- BAGIAN PETA -->
    <div class="col-md-7 mb-3">
        <div class="card shadow-sm p-3 h-100">
            <h5 class="fw-bold">Peta Laporan</h5>
            <p class="text-secondary small">Titik dan garis hanya muncul jika pelapor mengisi koordinat atau menggambar garis.</p>
            <div id="map" style="height: 300px;"></div>
        </div>
    </div>
</div>

<!-- TABEL LAPORAN & PENCARIAN -->
<div class="card shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Daftar Laporan</h5>

        <form action="{{ route('reports.index') }}" method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari judul / alamat..." value="{{ request('search') }}">
            <select name="status" class="form-select form-select-sm" style="width: 130px;">
                <option value="Semua" @selected(request('status') == 'Semua')>Semua Status</option>
                <option value="Menunggu" @selected(request('status') == 'Menunggu')>Menunggu</option>
                <option value="Diverifikasi" @selected(request('status') == 'Diverifikasi')>Diverifikasi</option>
                <option value="Diproses" @selected(request('status') == 'Diproses')>Diproses</option>
                <option value="Selesai" @selected(request('status') == 'Selesai')>Selesai</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Cari</button>
            @if(request('search') || (request('status') && request('status') !== 'Semua'))
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-light">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Jenis</th>
                    <th>Alamat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                <tr>
                    <td>{{ $report->title }}</td>
                    <td>{{ $report->infrastructure_type }}</td>
                    <td>{{ $report->address }}</td>
                    <td><span class="badge text-bg-secondary">{{ $report->status }}</span></td>
                    <td><a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-outline-primary">Lihat</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-secondary">Tidak ada laporan yang cocok dengan pencarianmu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<!-- Script Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // === 1. SCRIPT UNTUK GRAFIK ===
    const countJalan = {{ $reports->where('infrastructure_type', 'Jalan')->count() }};
    const countDrainase = {{ $reports->where('infrastructure_type', 'Drainase')->count() }};

    const ctx = document.getElementById('chartJenis').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut', // Jenis grafik: donat
        data: {
            labels: ['Jalan', 'Drainase'],
            datasets: [{
                data: [countJalan, countDrainase],
                backgroundColor: ['#0d6efd', '#198754'], // Warna Biru dan Hijau
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // === 2. SCRIPT UNTUK PETA ===
    const rawData = {!! json_encode($reports->toArray()) !!};

    const map = L.map('map').setView([-7.5, 110.8], 8);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    rawData.forEach(r => {
        if (r.latitude && r.longitude) {
            L.marker([parseFloat(r.latitude), parseFloat(r.longitude)]).addTo(map)
             .bindPopup(`<b>${r.title}</b><br>Status: ${r.status}`);
        }

        if (r.geometry) {
            try {
                const coords = JSON.parse(r.geometry);
                const latlngs = coords.map(c => [c.lat, c.lng]);
                L.polyline(latlngs, {color: 'red', weight: 5}).addTo(map)
                 .bindPopup(`<b>${r.title}</b><br>Ruas jalan rusak`);
            } catch (e) {
                console.error("Format geometri tidak valid");
            }
        }
    });
</script>
@endpush
