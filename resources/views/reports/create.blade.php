@extends('layouts.app')

@section('title', 'Buat Laporan')

@push('styles')
    <!-- Tambahan CSS Peta & Leaflet Draw -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <style>
        #map-picker { height: 400px; border-radius: 10px; margin-top: 10px; border: 1px solid #ccc; }
    </style>
@endpush

@section('content')
<div class="card shadow-sm p-4">
    <h2 class="fw-bold mb-1">Laporkan Kerusakan</h2>
    <p class="text-secondary">Isi informasi sesuai kondisi yang kamu lihat.</p>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nama pelapor</label>
            <input class="form-control" name="reporter_name" value="{{ old('reporter_name') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Kontak (opsional)</label>
            <input class="form-control" name="reporter_contact" value="{{ old('reporter_contact') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Jenis infrastruktur</label>
            <select class="form-select" name="infrastructure_type" required>
                <option value="">Pilih jenis</option>
                <option value="Jalan" @selected(old('infrastructure_type') == 'Jalan')>Jalan</option>
                <option value="Drainase" @selected(old('infrastructure_type') == 'Drainase')>Drainase</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Judul laporan</label>
            <input class="form-control" name="title" value="{{ old('title') }}" placeholder="Contoh: Jalan berlubang di depan balai desa" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi kerusakan</label>
            <textarea class="form-control" name="description" rows="4" required>{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Alamat / lokasi</label>
            <input class="form-control" name="address" value="{{ old('address') }}" placeholder="Nama jalan, desa, kecamatan" required>
        </div>

        <!-- BAGIAN PETA INTERAKTIF & GARIS -->
        <div class="mb-3 p-3 bg-light rounded border">
            <label class="form-label fw-bold">📍 Tandai Lokasi (Klik Peta atau Gambar Garis Jalan)</label>
            <p class="text-secondary small mb-2">Klik peta untuk menaruh titik (pin), ATAU gunakan ikon garis di pojok kiri peta untuk menggambar ruas jalan yang rusak.</p>
            <div id="map-picker"></div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Latitude (Titik Tengah)</label>
                <input class="form-control" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="Otomatis">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Longitude (Titik Tengah)</label>
                <input class="form-control" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="Otomatis">
            </div>
        </div>

        <!-- Hidden input untuk menyimpan data garis -->
        <input type="hidden" id="geometry" name="geometry" value="{{ old('geometry') }}">

        <!-- BAGIAN TRAFFIC IMPACT -->
        <div class="row bg-light p-3 rounded border mb-4 mt-2">
            <h6 class="fw-bold text-primary">🚦 Info Dampak Lalu Lintas (Traffic Impact)</h6>
            <p class="text-secondary small mb-2">Kosongkan jika kerusakan bukan di jalan raya.</p>

            <div class="col-md-6 mb-3">
                <label class="form-label">Lebar Jalan yang Masih Bisa Dilewati (meter)</label>
                <input type="number" step="0.1" class="form-control" name="remaining_width" value="{{ old('remaining_width') }}" placeholder="Contoh: 3.5">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Volume Kendaraan (kendaraan / jam)</label>
                <input type="number" class="form-control" name="traffic_volume" value="{{ old('traffic_volume') }}" placeholder="Contoh: 500">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Foto kondisi (opsional, maks 2MB)</label>
            <input class="form-control" type="file" name="photo" accept="image/*">
        </div>

        <button class="btn btn-primary px-4">Kirim Laporan</button>
        <a href="{{ route('reports.index') }}" class="btn btn-light">Batal</a>
    </form>
</div>
@endsection

@push('scripts')
    <!-- Script Peta & Leaflet Draw -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script>
        const initialLat = -7.5;
        const initialLng = 110.8;

        const map = L.map('map-picker').setView([initialLat, initialLng], 8);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Fitur Gambar (Draw)
        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        const drawControl = new L.Control.Draw({
            draw: {
                polyline: true, // Hanya izinkan gambar garis
                polygon: false,
                circle: false,
                rectangle: false,
                marker: true, // Tetap izinkan pin
                circlemarker: false
            },
            edit: {
                featureGroup: drawnItems
            }
        });
        map.addControl(drawControl);

        // Event saat selesai menggambar garis / pin
        map.on(L.Draw.Event.CREATED, function (e) {
            const type = e.layerType;
            const layer = e.layer;

            // Hapus gambar sebelumnya (biar cuma bisa 1 gambar per laporan)
            drawnItems.clearLayers();
            drawnItems.addLayer(layer);

            if (type === 'polyline') {
                // Jika menggambar garis, simpan koordinatnya ke input hidden
                const latlngs = layer.getLatLngs();
                const geometryData = JSON.stringify(latlngs);
                document.getElementById('geometry').value = geometryData;

                // Ambil titik tengah sebagai koordinat pin
                const bounds = layer.getBounds();
                const center = bounds.getCenter();
                document.getElementById('latitude').value = center.lat;
                document.getElementById('longitude').value = center.lng;

            } else if (type === 'marker') {
                // Jika hanya menaruh pin
                const latlng = layer.getLatLng();
                document.getElementById('latitude').value = latlng.lat;
                document.getElementById('longitude').value = latlng.lng;
                document.getElementById('geometry').value = ''; // Kosongkan garis
            }
        });
    </script>
@endpush
