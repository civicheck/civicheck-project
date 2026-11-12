@extends('layouts.app')

@section('title', 'Detail Laporan')

@section('content')
<div class="card shadow-sm p-4">
    <div class="mb-3">
        <a href="{{ route('reports.index') }}" class="text-decoration-none">&larr; Kembali ke dashboard</a>
    </div>

    <h2 class="fw-bold">{{ $report->title }}</h2>
    <p class="text-secondary">{{ $report->infrastructure_type }}</p>

    <p>{{ $report->description }}</p>

    <p><b>Pelapor:</b> {{ $report->reporter_name }}</p>
    <p><b>Alamat:</b> {{ $report->address }}</p>
    <p>
        <b>Status:</b> <span class="badge text-bg-secondary">{{ $report->status }}</span> <br>
        <b>Prioritas:</b> <span class="badge text-bg-info">{{ $report->priority }}</span>
    </p>

    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <h5 class="fw-bold">📸 Foto Kondisi Awal (Before)</h5>
            @if($report->photo_path)
                <img src="{{ asset('storage/' . $report->photo_path) }}" class="img-fluid rounded border" style="max-height:300px; width:100%; object-fit:cover;">
            @else
                <div class="alert alert-secondary text-center">Tidak ada foto awal.</div>
            @endif
        </div>

        <div class="col-md-6 mb-3">
            <h5 class="fw-bold text-success">✅ Foto Hasil Perbaikan (After)</h5>
            @if($report->after_photo_path)
                <img src="{{ asset('storage/' . $report->after_photo_path) }}" class="img-fluid rounded border" style="max-height:300px; width:100%; object-fit:cover;">
            @else
                <div class="alert alert-warning text-center">Belum ada bukti perbaikan.</div>
            @endif
        </div>
    </div>

    <!-- FORM UPLOAD AFTER -->
    @if(auth()->check() && auth()->user()->role === 'petugas')
    <div class="card bg-light border-0 mt-2 mb-4">
        <div class="card-body">
            <h6 class="fw-bold">Unggah Bukti Perbaikan (Simulasi Petugas)</h6>
            <form action="{{ route('reports.after', $report) }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                @csrf
                <input class="form-control" type="file" name="after_photo" accept="image/*" required>
                <button class="btn btn-success text-nowrap">Upload Foto After</button>
            </form>
            @error('after_photo')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
    @endif

    <div class="mt-3">
        <h5 class="fw-bold">Peta Lokasi</h5>
        <div id="map-detail"></div>
    </div>

    @if($report->traffic_volume && $report->remaining_width)
        <div class="alert alert-info mt-4">
            <h5 class="fw-bold">🚦 Analisis Dampak Lalu Lintas</h5>
            @php
                $kapasitasPerMeter = 1500 / 3.5;
                $kapasitasSisa = $kapasitasPerMeter * $report->remaining_width;
                $pembagi = max(1, $kapasitasSisa);
                $rasio = $report->traffic_volume / $pembagi;
            @endphp
            <p class="mb-1">Lebar sisa: <b>{{ $report->remaining_width }} meter</b> | Volume: <b>{{ $report->traffic_volume }} kend/jam</b></p>
            @if($rasio > 1)
                <p class="text-danger fw-bold mb-0">⚠️ TRAFFIC JAM: Volume melebihi kapasitas sisa! Rasio (V/C) = {{ number_format($rasio, 2) }}. Perbaikan jalan ini SANGAT MENDESAK.</p>
            @else
                <p class="text-success fw-bold mb-0">✅ Lalu lintas masih terkendali. Rasio (V/C) = {{ number_format($rasio, 2) }}.</p>
            @endif
        </div>
    @endif

    <!-- KALKULATOR MATERIAL (RAB) -->
    @if(auth()->check() && auth()->user()->role === 'petugas')
    <div class="card bg-light border-0 mt-4 mb-4">
        <div class="card-body">
            <h5 class="fw-bold">🏗️ Kalkulator Kebutuhan Material (RAB)</h5>
            <p class="text-secondary small">Masukkan dimensi kerusakan untuk menghitung kebutuhan aspal.</p>

            <form action="{{ route('reports.material', $report) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Panjang (m)</label>
                        <input type="number" step="0.01" class="form-control" name="damage_length" value="{{ $report->damage_length }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Lebar (m)</label>
                        <input type="number" step="0.01" class="form-control" name="damage_width" value="{{ $report->damage_width }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tebal/Kedalaman (cm)</label>
                        <input type="number" step="0.01" class="form-control" name="damage_depth" value="{{ $report->damage_depth }}" required>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-warning w-100 fw-bold">Hitung Material</button>
                    </div>
                </div>
            </form>

            @if($report->damage_length && $report->damage_width && $report->damage_depth)
                @php
                    // Konversi cm ke meter
                    $tebal_m = $report->damage_depth / 100;
                    // Hitung Volume (m3)
                    $volume = $report->damage_length * $report->damage_width * $tebal_m;
                    // Berat jenis aspal padat rata-rata = 2.3 Ton/m3
                    $berat_aspal = $volume * 2.3;
                @endphp

                <div class="alert alert-success mt-3 mb-0">
                    <h6 class="fw-bold mb-2">📊 Hasil Perhitungan:</h6>
                    <ul class="mb-0">
                        <li>Volume Pekerjaan: <b>{{ number_format($volume, 3) }} m³</b></li>
                        <li>Estimasi Kebutuhan Aspal: <b>{{ number_format($berat_aspal, 2) }} Ton</b></li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- FORM UBAH STATUS -->
    @if(auth()->check() && auth()->user()->role === 'petugas')
    <hr class="my-4">
    <h5 class="fw-bold">Perbarui status (simulasi petugas)</h5>
    <form action="{{ route('reports.status', $report) }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    @foreach(['Menunggu','Diverifikasi','Diproses','Selesai'] as $s)
                        <option value="{{ $s }}" @selected($report->status === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Prioritas</label>
                <select class="form-select" name="priority">
                    @foreach(['Rendah','Sedang','Tinggi'] as $p)
                        <option value="{{ $p }}" @selected($report->priority === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button class="btn btn-primary mt-3">Simpan perubahan</button>
    </form>
    @endif
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>#map-detail { height: 300px; border-radius: 10px; margin-top: 15px; }</style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const latAwal = {{ $report->latitude ?? -7.5 }};
        const lngAwal = {{ $report->longitude ?? 110.8 }};
        const mapDetail = L.map('map-detail').setView([latAwal, lngAwal], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(mapDetail);
        @if($report->geometry)
            const coords = {!! $report->geometry !!};
            const latlngs = coords.map(c => [c.lat, c.lng]);
            L.polyline(latlngs, {color: 'red', weight: 5}).addTo(mapDetail);
            mapDetail.fitBounds(L.polyline(latlngs).getBounds());
        @elseif($report->latitude && $report->longitude)
            L.marker([{{ $report->latitude }}, {{ $report->longitude }}]).addTo(mapDetail);
        @endif
    </script>
@endpush
